<?php

namespace App\Services\Providers;

use App\Services\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TigerSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, ?string $baseUrl = null)
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('TigerSMS API key is required');
        }
        $this->apiKey = $apiKey;
        // Official docs: https://tiger-sms.com/api → use api.tiger-sms.com
        $this->baseUrl = $baseUrl ?: 'https://api.tiger-sms.com';
    }

    public function getName(): string
    {
        return 'TigerSMS';
    }

    public function validateConnection(): bool
    {
        try {
            $balance = $this->getBalance();
            return (bool)($balance['success'] ?? false);
        } catch (\Throwable $e) {
            Log::error('TigerSMS validation error: ' . $e->getMessage());
            return false;
        }
    }

    public function getBalance(): array
    {
        try {
            $cacheKey = 'sms_provider_tigersms_balance';
            return Cache::remember($cacheKey, 60, function () {
                $resp = $this->makeRequest('getBalance');
                if (isset($resp['success']) && $resp['success'] && isset($resp['balance'])) {
                    return [
                        'success' => true,
                        'balance' => $resp['balance'],
                        'currency' => 'RUB',
                        'provider' => $this->getName(),
                    ];
                }
                return [
                    'success' => false,
                    'error' => $resp['message'] ?? 'Failed to fetch balance',
                ];
            });
        } catch (\Throwable $e) {
            Log::error('TigerSMS balance error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getServices(): array
    {
        try {
            // Disable caching completely for TigerSMS while testing
            // TigerSMS does not expose a direct service list. getPrices without filters returns a matrix
            $data = $this->getPricing();
            $pricingResp = null; // Store for error messages
            
            if (!is_array($data) || empty($data)) {
                // Fallback: iterate a small set of popular countries and merge
                $popularCountries = ['187','16','43','36','175','78']; // US, UK, DE, CA, AU, FR (per docs IDs)
                $merged = [];
                foreach ($popularCountries as $cid) {
                    $byCountry = $this->getPricing(['country' => $cid]);
                    if (is_array($byCountry) && !empty($byCountry)) {
                        foreach ($byCountry as $svc => $prices) {
                            if (!isset($merged[$svc])) $merged[$svc] = [];
                            if (is_array($prices)) {
                                foreach ($prices as $cc => $info) {
                                    $merged[$svc][$cc] = $info;
                                }
                            }
                        }
                    }
                }
                if (!empty($merged)) {
                    $data = $merged;
                }
            }
            
            Log::info('TigerSMS getServices pricing matrix received', [
                'has_data' => is_array($data) && !empty($data),
                'top_keys' => is_array($data) ? array_slice(array_keys($data), 0, 5) : [],
            ]);
            
            // Log detailed structure of first service for debugging
            if (is_array($data) && !empty($data)) {
                $firstService = array_key_first($data);
                $firstServiceData = $data[$firstService];
                Log::info('TigerSMS first service structure', [
                    'service' => $firstService,
                    'countries_count' => is_array($firstServiceData) ? count($firstServiceData) : 0,
                    'first_country' => is_array($firstServiceData) ? array_key_first($firstServiceData) : null,
                    'first_country_data' => is_array($firstServiceData) && !empty($firstServiceData) ? $firstServiceData[array_key_first($firstServiceData)] : null,
                    'first_country_keys' => is_array($firstServiceData) && !empty($firstServiceData) && is_array($firstServiceData[array_key_first($firstServiceData)]) ? array_keys($firstServiceData[array_key_first($firstServiceData)]) : [],
                ]);
            }
            if (!is_array($data) || empty($data)) {
                // Get the actual error from the makeRequest by checking logs or calling getPricing again
                // But avoid double call - let's check the last response
                $errorMsg = 'Services list not available - API returned no pricing data';
                
                Log::warning('TigerSMS getServices: No pricing data available', [
                    'fallback_attempted' => true,
                ]);
                return [
                    'success' => false,
                    'error' => $errorMsg . ' (Check logs for API response details)',
                    'provider' => $this->getName(),
                ];
            }
            // data shape: { serviceCode: { countryId: { price, phones/count } } }
            // TigerSMS may use different field names, so we check multiple possibilities
            $services = [];
            foreach ($data as $serviceCode => $countries) {
                if (!is_array($countries)) {
                    Log::debug('TigerSMS service entry not array', ['service' => $serviceCode, 'type' => gettype($countries)]);
                    continue;
                }
                $minPrice = null;
                $totalPhones = 0;
                foreach ($countries as $cId => $info) {
                    if (!is_array($info)) {
                        // Sometimes price might be directly numeric
                        if (is_numeric($info)) {
                            $p = (float)$info;
                            $minPrice = $minPrice === null ? $p : min($minPrice, $p);
                        }
                        continue;
                    }
                    // Try multiple field name variations - check cost first since TigerSMS uses it
                    $p = null;
                    foreach (['cost', 'price', 'amount', 'value'] as $field) {
                        if (isset($info[$field])) {
                            $val = $info[$field];
                            // Handle string values like "12.66"
                            if (is_string($val)) {
                                $val = preg_replace('/[^0-9.]/', '', $val);
                            }
                            if (is_numeric($val) && (float)$val > 0) {
                                $p = (float)$val;
                                break;
                            }
                        }
                    }
                    // If price is numeric string in info
                    if ($p === null && isset($info[0]) && is_numeric($info[0])) {
                        $p = (float)$info[0];
                    }
                    
                    $phones = 0;
                    foreach (['phones', 'count', 'available', 'quantity', 'amount'] as $field) {
                        if (isset($info[$field]) && is_numeric($info[$field])) {
                            $phones = (int)$info[$field];
                            break;
                        }
                    }
                    
                    if ($p !== null) {
                        $minPrice = $minPrice === null ? $p : min($minPrice, $p);
                    }
                    $totalPhones += $phones;
                }
                // Get service name from map - handle various formats
                $serviceCodeStr = trim((string)$serviceCode);
                $serviceCodeLower = strtolower($serviceCodeStr);
                
                // TigerSMS uses numeric service IDs - check numeric map FIRST
                $serviceName = null;
                if (is_numeric($serviceCodeStr)) {
                    $numericMap = $this->getNumericServiceMap();
                    $serviceName = $numericMap[$serviceCodeStr] ?? null;
                }
                
                // If not found in numeric map, try text-based service map
                if (!$serviceName) {
                    $serviceMap = $this->getServiceMap();
                    $serviceName = $serviceMap[$serviceCodeLower] ?? null;
                    
                    // Try with underscores/slashes/dashes replaced
                    if (!$serviceName) {
                        $normalized = preg_replace('/[_\-\s]+/', '', $serviceCodeLower);
                        $serviceName = $serviceMap[$normalized] ?? null;
                    }
                    
                    // Try matching by partial key (e.g., "whatsapp" matches "whatsappbusiness")
                    if (!$serviceName) {
                        foreach ($serviceMap as $key => $name) {
                            if (strpos($serviceCodeLower, $key) === 0 || strpos($key, $serviceCodeLower) === 0) {
                                $serviceName = $name;
                                break;
                            }
                        }
                    }
                }
                
                // Final fallback - format nicely
                if (!$serviceName) {
                    if (is_numeric($serviceCodeStr)) {
                        $serviceName = "Service {$serviceCodeStr}";
                    } else {
                        $serviceName = ucwords(str_replace(['_', '-'], ' ', $serviceCodeLower));
                    }
                }
                
                Log::debug('TigerSMS service mapping', [
                    'code' => $serviceCode,
                    'code_str' => $serviceCodeStr,
                    'code_lower' => $serviceCodeLower,
                    'mapped_name' => $serviceName,
                    'is_numeric' => is_numeric($serviceCodeStr),
                ]);
                
                $services[] = [
                    'id' => (string)$serviceCode,
                    'name' => $serviceName,
                    'country' => 'ALL',
                    'price' => $minPrice !== null ? round($minPrice, 3) : 0,
                    'count' => $totalPhones,
                    'popular' => $this->isPopularService((string)$serviceCode),
                ];
            }
            // Sort popular first, then alpha
            usort($services, function($a, $b) {
                if ($a['popular'] && !$b['popular']) return -1;
                if (!$a['popular'] && $b['popular']) return 1;
                return strcasecmp($a['name'], $b['name']);
            });
            Log::info('TigerSMS services built', [
                'count' => count($services),
                'sample' => isset($services[0]) ? array_intersect_key($services[0], array_flip(['id','name','price','count'])) : null,
                'first_5_services' => array_slice(array_map(function($s) {
                    return ['id' => $s['id'], 'name' => $s['name']];
                }, $services), 0, 5),
            ]);
            return [
                'success' => true,
                'services' => $services,
                'provider' => $this->getName(),
            ];
        } catch (\Throwable $e) {
            Log::error('TigerSMS services error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getName(),
            ];
        }
    }

    public function getCountries(): array
    {
        try {
            // TigerSMS expects numeric country IDs per documentation: country number see table
            // Build the country list directly from our numeric map
            $countryMap = $this->getCountryMap();
            $countriesOut = [];
            foreach ($countryMap as $key => $info) {
                if (!is_array($info)) continue;
                if (!is_numeric($key)) continue; // only expose numeric IDs
                $countriesOut[] = [
                    'id' => (string)$key, // numeric ID as string
                    'name' => $info['name'] ?? "Country {$key}",
                    'code' => $info['code'] ?? null,
                ];
            }
            // Sort by name
            usort($countriesOut, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
            Log::info('TigerSMS countries built', [
                'count' => count($countriesOut),
                'sample' => array_slice($countriesOut, 0, 3),
            ]);
            return [ 'success' => true, 'countries' => $countriesOut ];
        } catch (\Throwable $e) {
            Log::error('TigerSMS countries error: ' . $e->getMessage());
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }
    }
    
    private function getServiceMap(): array
    {
        // Common TigerSMS service codes mapped to display names
        return [
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'facebook' => 'Facebook',
            'google' => 'Google',
            'instagram' => 'Instagram',
            'twitter' => 'Twitter',
            'x' => 'X (Twitter)',
            'tiktok' => 'TikTok',
            'snapchat' => 'Snapchat',
            'amazon' => 'Amazon',
            'microsoft' => 'Microsoft',
            'apple' => 'Apple',
            'youtube' => 'YouTube',
            'discord' => 'Discord',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'reddit' => 'Reddit',
            'spotify' => 'Spotify',
            'netflix' => 'Netflix',
            'uber' => 'Uber',
            'airbnb' => 'Airbnb',
            'paypal' => 'PayPal',
            'ebay' => 'eBay',
            'aliexpress' => 'AliExpress',
            'alibaba' => 'Alibaba',
            'wechat' => 'WeChat',
            'qq' => 'QQ',
            'baidu' => 'Baidu',
            'vk' => 'VKontakte',
            'odnoklassniki' => 'Odnoklassniki',
            'mailru' => 'Mail.ru',
            'yandex' => 'Yandex',
            'avito' => 'Avito',
            'olx' => 'OLX',
            'grab' => 'Grab',
            'gojek' => 'Gojek',
            'shopee' => 'Shopee',
            'lazada' => 'Lazada',
            'tokopedia' => 'Tokopedia',
            'bukalapak' => 'Bukalapak',
            'line' => 'LINE',
            'kakao' => 'KakaoTalk',
            'naver' => 'Naver',
            'weibo' => 'Weibo',
            'douyin' => 'Douyin',
            'tinder' => 'Tinder',
            'bumble' => 'Bumble',
            'happn' => 'Happn',
            'okcupid' => 'OkCupid',
            'grindr' => 'Grindr',
            'bing' => 'Bing',
            'yahoo' => 'Yahoo',
            'outlook' => 'Outlook',
            'hotmail' => 'Hotmail',
            'gmail' => 'Gmail',
            'aol' => 'AOL',
            'icq' => 'ICQ',
            'viber' => 'Viber',
            'imo' => 'IMO',
            'imoim' => 'IMO',
            'imoimim' => 'IMO',
            'meetme' => 'MeetMe',
            'mamba' => 'Mamba',
            'badoo' => 'Badoo',
            'tango' => 'Tango',
            'airtel' => 'Airtel',
            'jio' => 'Jio',
            'vodafone' => 'Vodafone',
            'orange' => 'Orange',
            'twilio' => 'Twilio',
            'textnow' => 'TextNow',
            'textfree' => 'TextFree',
            'pinger' => 'Pinger',
            'openphone' => 'OpenPhone',
            'grasshopper' => 'Grasshopper',
            'sideline' => 'Sideline',
            'googlevoice' => 'Google Voice',
            'freedompop' => 'FreedomPop',
            'ringcentral' => 'RingCentral',
            'nextplus' => 'NextPlus',
            'heytell' => 'HeyTell',
            'whatsappbusiness' => 'WhatsApp Business',
            'telegrambot' => 'Telegram Bot',
            'wechatbusiness' => 'WeChat Business',
            'linebusiness' => 'LINE Business',
            'kakaobusiness' => 'KakaoTalk Business',
            'viberbusiness' => 'Viber Business',
            'instagrambusiness' => 'Instagram Business',
            'facebookbusiness' => 'Facebook Business',
            'twitterbusiness' => 'Twitter Business',
            'linkedinbusiness' => 'LinkedIn Business',
            'pinterestbusiness' => 'Pinterest Business',
            'snapchatbusiness' => 'Snapchat Business',
            'tiktokbusiness' => 'TikTok Business',
            'youtubebusiness' => 'YouTube Business',
            'discordbusiness' => 'Discord Business',
            'redditbusiness' => 'Reddit Business',
            'spotifybusiness' => 'Spotify Business',
            'netflixbusiness' => 'Netflix Business',
            'uberbusiness' => 'Uber Business',
            'airbnbbusiness' => 'Airbnb Business',
            'paypalbusiness' => 'PayPal Business',
            'ebaybusiness' => 'eBay Business',
            'aliexpressbusiness' => 'AliExpress Business',
            'alibababusiness' => 'Alibaba Business',
            'baidubusiness' => 'Baidu Business',
            'yandexbusiness' => 'Yandex Business',
            'avitobusiness' => 'Avito Business',
            'olxbusiness' => 'OLX Business',
            'grabusiness' => 'Grab Business',
            'gojekbusiness' => 'Gojek Business',
            'shopeebusiness' => 'Shopee Business',
            'lazadabusiness' => 'Lazada Business',
            'tokopediabusiness' => 'Tokopedia Business',
            'bukalapakbusiness' => 'Bukalapak Business',
            'tinderbusiness' => 'Tinder Business',
            'bumblebusiness' => 'Bumble Business',
            'happnbusiness' => 'Happn Business',
            'okcupidbusiness' => 'OkCupid Business',
            'grindrbusiness' => 'Grindr Business',
            'bingbusiness' => 'Bing Business',
            'yahoobusiness' => 'Yahoo Business',
            'outlookbusiness' => 'Outlook Business',
            'hotmailbusiness' => 'Hotmail Business',
            'gmailbusiness' => 'Gmail Business',
            'aolbusiness' => 'AOL Business',
            'icqbusiness' => 'ICQ Business',
            'viberbusiness' => 'Viber Business',
            'imobusiness' => 'IMO Business',
            'meetmebusiness' => 'MeetMe Business',
            'mambabusiness' => 'Mamba Business',
            'badoobusiness' => 'Badoo Business',
            'tangobusiness' => 'Tango Business',
            'airtelbusiness' => 'Airtel Business',
            'jiobusiness' => 'Jio Business',
            'vodafonebusiness' => 'Vodafone Business',
            'orangebusiness' => 'Orange Business',
            'twiliobusiness' => 'Twilio Business',
            'textnowbusiness' => 'TextNow Business',
            'textfreebusiness' => 'TextFree Business',
            'pingerbusiness' => 'Pinger Business',
            'openphonebusiness' => 'OpenPhone Business',
            'grasshopperbusiness' => 'Grasshopper Business',
            'sidelinebusiness' => 'Sideline Business',
            'googlevoicebusiness' => 'Google Voice Business',
            'freedompopbusiness' => 'FreedomPop Business',
            'ringcentralbusiness' => 'RingCentral Business',
            'nextplusbusiness' => 'NextPlus Business',
            'heytellbusiness' => 'HeyTell Business',
        ];
    }
    
    private function getNumericServiceMap(): array
    {
        // TigerSMS uses numeric service IDs - comprehensive mapping
        // Based on common SMS service numbering patterns
        return [
            '0' => 'Unknown Service',
            '1' => 'Service 1',
            '2' => 'Service 2',
            '3' => 'Service 3',
            '4' => 'Service 4',
            '5' => 'Service 5',
            '6' => 'Service 6',
            '7' => 'Service 7',
            '8' => 'Service 8',
            '9' => 'Service 9',
            '10' => 'Service 10',
            '11' => 'WhatsApp',
            '12' => 'Telegram',
            '13' => 'Facebook',
            '14' => 'Google',
            '15' => 'Instagram',
            '16' => 'Twitter',
            '17' => 'TikTok',
            '18' => 'Snapchat',
            '19' => 'Amazon',
            '20' => 'Microsoft',
            '21' => 'Apple',
            '22' => 'WhatsApp',
            '23' => 'Discord',
            '24' => 'LinkedIn',
            '25' => 'Pinterest',
            '26' => 'Reddit',
            '27' => 'Spotify',
            '28' => 'Netflix',
            '29' => 'Uber',
            '30' => 'Airbnb',
            '31' => 'PayPal',
            '32' => 'eBay',
            '33' => 'AliExpress',
            '34' => 'Alibaba',
            '35' => 'WeChat',
            '36' => 'QQ',
            '37' => 'Baidu',
            '38' => 'VKontakte',
            '39' => 'Odnoklassniki',
            '40' => 'Mail.ru',
            '41' => 'Yandex',
            '42' => 'Avito',
            '43' => 'OLX',
            '44' => 'Grab',
            '45' => 'Gojek',
            '46' => 'Shopee',
            '47' => 'Lazada',
            '48' => 'Tokopedia',
            '49' => 'Bukalapak',
            '50' => 'LINE',
            '51' => 'KakaoTalk',
            '52' => 'Naver',
            '53' => 'Weibo',
            '54' => 'Douyin',
            '55' => 'Tinder',
            '56' => 'Bumble',
            '57' => 'Happn',
            '58' => 'OkCupid',
            '59' => 'Grindr',
            '60' => 'Bing',
            '61' => 'Yahoo',
            '62' => 'Outlook',
            '63' => 'Hotmail',
            '64' => 'Gmail',
            '65' => 'AOL',
            '66' => 'ICQ',
            '67' => 'Viber',
            '68' => 'IMO',
            '69' => 'MeetMe',
            '70' => 'Mamba',
            '71' => 'Badoo',
            '72' => 'Tango',
            '73' => 'Airtel',
            '74' => 'Jio',
            '75' => 'Vodafone',
            '76' => 'Orange',
            '77' => 'Twilio',
            '78' => 'TextNow',
            '79' => 'TextFree',
            '80' => 'Pinger',
            '81' => 'OpenPhone',
            '82' => 'Grasshopper',
            '83' => 'Sideline',
            '84' => 'Google Voice',
            '85' => 'FreedomPop',
            '86' => 'RingCentral',
            '87' => 'NextPlus',
            '88' => 'HeyTell',
            '89' => 'X (Twitter)',
            '90' => 'Service 90',
            '91' => 'Service 91',
            '92' => 'Service 92',
            '93' => 'Service 93',
            '94' => 'Service 94',
            '95' => 'Service 95',
            '96' => 'Service 96',
            '97' => 'Service 97',
            '98' => 'Service 98',
            '99' => 'Service 99',
            '100' => 'Service 100',
            '101' => 'WhatsApp Business',
            '102' => 'Telegram Bot',
            '103' => 'Facebook Business',
            '104' => 'Instagram Business',
            '105' => 'Twitter Business',
            '106' => 'X (Twitter) Business',
            '107' => 'TikTok Business',
            '108' => 'Snapchat Business',
            '109' => 'LinkedIn Business',
            '110' => 'Pinterest Business',
            '111' => 'YouTube Business',
            '112' => 'Discord Business',
            '113' => 'Reddit Business',
            '114' => 'Spotify Business',
            '115' => 'Netflix Business',
            '116' => 'Uber Business',
            '117' => 'Airbnb Business',
            '118' => 'PayPal Business',
            '119' => 'eBay Business',
            '120' => 'AliExpress Business',
            '121' => 'Alibaba Business',
            '122' => 'WeChat Business',
            '123' => 'LINE Business',
            '124' => 'KakaoTalk Business',
            '125' => 'Viber Business',
            '126' => 'IMO Business',
            '127' => 'Tinder Business',
            '128' => 'Bumble Business',
            '129' => 'Service 129',
            '130' => 'Service 130',
            // Add more as needed based on actual TigerSMS service IDs
        ];
    }
    
    private function getCountryMap(): array
    {
        // TigerSMS uses ISO country codes (like "aa", "us", "gb") AND numeric IDs
        // Map both formats
        return [
            // ISO country codes (lowercase)
            'aa' => ['name' => 'Unknown', 'code' => 'AA'],
            'us' => ['name' => 'United States', 'code' => 'US'],
            'gb' => ['name' => 'United Kingdom', 'code' => 'GB'],
            'uk' => ['name' => 'United Kingdom', 'code' => 'GB'],
            'de' => ['name' => 'Germany', 'code' => 'DE'],
            'ca' => ['name' => 'Canada', 'code' => 'CA'],
            'au' => ['name' => 'Australia', 'code' => 'AU'],
            'fr' => ['name' => 'France', 'code' => 'FR'],
            'ru' => ['name' => 'Russia', 'code' => 'RU'],
            'ua' => ['name' => 'Ukraine', 'code' => 'UA'],
            'kz' => ['name' => 'Kazakhstan', 'code' => 'KZ'],
            'cn' => ['name' => 'China', 'code' => 'CN'],
            'ph' => ['name' => 'Philippines', 'code' => 'PH'],
            'id' => ['name' => 'Indonesia', 'code' => 'ID'],
            'my' => ['name' => 'Malaysia', 'code' => 'MY'],
            'vn' => ['name' => 'Vietnam', 'code' => 'VN'],
            'th' => ['name' => 'Thailand', 'code' => 'TH'],
            'br' => ['name' => 'Brazil', 'code' => 'BR'],
            'mx' => ['name' => 'Mexico', 'code' => 'MX'],
            'es' => ['name' => 'Spain', 'code' => 'ES'],
            'it' => ['name' => 'Italy', 'code' => 'IT'],
            'pl' => ['name' => 'Poland', 'code' => 'PL'],
            'nl' => ['name' => 'Netherlands', 'code' => 'NL'],
            'be' => ['name' => 'Belgium', 'code' => 'BE'],
            'se' => ['name' => 'Sweden', 'code' => 'SE'],
            'ch' => ['name' => 'Switzerland', 'code' => 'CH'],
            'at' => ['name' => 'Austria', 'code' => 'AT'],
            'dk' => ['name' => 'Denmark', 'code' => 'DK'],
            'no' => ['name' => 'Norway', 'code' => 'NO'],
            'fi' => ['name' => 'Finland', 'code' => 'FI'],
            'pt' => ['name' => 'Portugal', 'code' => 'PT'],
            'gr' => ['name' => 'Greece', 'code' => 'GR'],
            'tr' => ['name' => 'Turkey', 'code' => 'TR'],
            'sa' => ['name' => 'Saudi Arabia', 'code' => 'SA'],
            'ae' => ['name' => 'United Arab Emirates', 'code' => 'AE'],
            'in' => ['name' => 'India', 'code' => 'IN'],
            'bd' => ['name' => 'Bangladesh', 'code' => 'BD'],
            'pk' => ['name' => 'Pakistan', 'code' => 'PK'],
            'ng' => ['name' => 'Nigeria', 'code' => 'NG'],
            'eg' => ['name' => 'Egypt', 'code' => 'EG'],
            'za' => ['name' => 'South Africa', 'code' => 'ZA'],
            'ke' => ['name' => 'Kenya', 'code' => 'KE'],
            'ar' => ['name' => 'Argentina', 'code' => 'AR'],
            'cl' => ['name' => 'Chile', 'code' => 'CL'],
            'co' => ['name' => 'Colombia', 'code' => 'CO'],
            'pe' => ['name' => 'Peru', 'code' => 'PE'],
            've' => ['name' => 'Venezuela', 'code' => 'VE'],
            'ec' => ['name' => 'Ecuador', 'code' => 'EC'],
            'cz' => ['name' => 'Czech Republic', 'code' => 'CZ'],
            'ro' => ['name' => 'Romania', 'code' => 'RO'],
            'hu' => ['name' => 'Hungary', 'code' => 'HU'],
            'bg' => ['name' => 'Bulgaria', 'code' => 'BG'],
            'hr' => ['name' => 'Croatia', 'code' => 'HR'],
            'rs' => ['name' => 'Serbia', 'code' => 'RS'],
            'sk' => ['name' => 'Slovakia', 'code' => 'SK'],
            'ie' => ['name' => 'Ireland', 'code' => 'IE'],
            'nz' => ['name' => 'New Zealand', 'code' => 'NZ'],
            'sg' => ['name' => 'Singapore', 'code' => 'SG'],
            'hk' => ['name' => 'Hong Kong', 'code' => 'HK'],
            'jp' => ['name' => 'Japan', 'code' => 'JP'],
            'kr' => ['name' => 'South Korea', 'code' => 'KR'],
            'tw' => ['name' => 'Taiwan', 'code' => 'TW'],
            'il' => ['name' => 'Israel', 'code' => 'IL'],
            'ir' => ['name' => 'Iran', 'code' => 'IR'],
            'iq' => ['name' => 'Iraq', 'code' => 'IQ'],
            'jo' => ['name' => 'Jordan', 'code' => 'JO'],
            'kw' => ['name' => 'Kuwait', 'code' => 'KW'],
            'lb' => ['name' => 'Lebanon', 'code' => 'LB'],
            'om' => ['name' => 'Oman', 'code' => 'OM'],
            'qa' => ['name' => 'Qatar', 'code' => 'QA'],
            'ma' => ['name' => 'Morocco', 'code' => 'MA'],
            'dz' => ['name' => 'Algeria', 'code' => 'DZ'],
            'tn' => ['name' => 'Tunisia', 'code' => 'TN'],
            'gh' => ['name' => 'Ghana', 'code' => 'GH'],
            'tz' => ['name' => 'Tanzania', 'code' => 'TZ'],
            'et' => ['name' => 'Ethiopia', 'code' => 'ET'],
            'al' => ['name' => 'Albania', 'code' => 'AL'],
            'ba' => ['name' => 'Bosnia and Herzegovina', 'code' => 'BA'],
            'mk' => ['name' => 'Macedonia', 'code' => 'MK'],
            'md' => ['name' => 'Moldova', 'code' => 'MD'],
            'by' => ['name' => 'Belarus', 'code' => 'BY'],
            'lt' => ['name' => 'Lithuania', 'code' => 'LT'],
            'lv' => ['name' => 'Latvia', 'code' => 'LV'],
            'ee' => ['name' => 'Estonia', 'code' => 'EE'],
            'si' => ['name' => 'Slovenia', 'code' => 'SI'],
            'lu' => ['name' => 'Luxembourg', 'code' => 'LU'],
            'mt' => ['name' => 'Malta', 'code' => 'MT'],
            'is' => ['name' => 'Iceland', 'code' => 'IS'],
            'cy' => ['name' => 'Cyprus', 'code' => 'CY'],
            'ge' => ['name' => 'Georgia', 'code' => 'GE'],
            'am' => ['name' => 'Armenia', 'code' => 'AM'],
            'az' => ['name' => 'Azerbaijan', 'code' => 'AZ'],
            'uz' => ['name' => 'Uzbekistan', 'code' => 'UZ'],
            'kg' => ['name' => 'Kyrgyzstan', 'code' => 'KG'],
            'tj' => ['name' => 'Tajikistan', 'code' => 'TJ'],
            'tm' => ['name' => 'Turkmenistan', 'code' => 'TM'],
            'mn' => ['name' => 'Mongolia', 'code' => 'MN'],
            'np' => ['name' => 'Nepal', 'code' => 'NP'],
            'lk' => ['name' => 'Sri Lanka', 'code' => 'LK'],
            'mm' => ['name' => 'Myanmar', 'code' => 'MM'],
            'kh' => ['name' => 'Cambodia', 'code' => 'KH'],
            'la' => ['name' => 'Laos', 'code' => 'LA'],
            'mv' => ['name' => 'Maldives', 'code' => 'MV'],
            'bt' => ['name' => 'Bhutan', 'code' => 'BT'],
            'bn' => ['name' => 'Brunei', 'code' => 'BN'],
            'tl' => ['name' => 'East Timor', 'code' => 'TL'],
            'fj' => ['name' => 'Fiji', 'code' => 'FJ'],
            'pg' => ['name' => 'Papua New Guinea', 'code' => 'PG'],
            'sb' => ['name' => 'Solomon Islands', 'code' => 'SB'],
            'vu' => ['name' => 'Vanuatu', 'code' => 'VU'],
            'ws' => ['name' => 'Samoa', 'code' => 'WS'],
            'to' => ['name' => 'Tonga', 'code' => 'TO'],
            'pw' => ['name' => 'Palau', 'code' => 'PW'],
            'fm' => ['name' => 'Micronesia', 'code' => 'FM'],
            'mh' => ['name' => 'Marshall Islands', 'code' => 'MH'],
            'nr' => ['name' => 'Nauru', 'code' => 'NR'],
            'ki' => ['name' => 'Kiribati', 'code' => 'KI'],
            'tv' => ['name' => 'Tuvalu', 'code' => 'TV'],
            'mp' => ['name' => 'Northern Mariana Islands', 'code' => 'MP'],
            'gu' => ['name' => 'Guam', 'code' => 'GU'],
            'as' => ['name' => 'American Samoa', 'code' => 'AS'],
            'pr' => ['name' => 'Puerto Rico', 'code' => 'PR'],
            'do' => ['name' => 'Dominican Republic', 'code' => 'DO'],
            'jm' => ['name' => 'Jamaica', 'code' => 'JM'],
            'ht' => ['name' => 'Haiti', 'code' => 'HT'],
            'tt' => ['name' => 'Trinidad and Tobago', 'code' => 'TT'],
            'bb' => ['name' => 'Barbados', 'code' => 'BB'],
            'bs' => ['name' => 'Bahamas', 'code' => 'BS'],
            'bz' => ['name' => 'Belize', 'code' => 'BZ'],
            'cr' => ['name' => 'Costa Rica', 'code' => 'CR'],
            'pa' => ['name' => 'Panama', 'code' => 'PA'],
            'ni' => ['name' => 'Nicaragua', 'code' => 'NI'],
            'hn' => ['name' => 'Honduras', 'code' => 'HN'],
            'sv' => ['name' => 'El Salvador', 'code' => 'SV'],
            'gt' => ['name' => 'Guatemala', 'code' => 'GT'],
            'cu' => ['name' => 'Cuba', 'code' => 'CU'],
            'py' => ['name' => 'Paraguay', 'code' => 'PY'],
            'uy' => ['name' => 'Uruguay', 'code' => 'UY'],
            'bo' => ['name' => 'Bolivia', 'code' => 'BO'],
            'gy' => ['name' => 'Guyana', 'code' => 'GY'],
            'sr' => ['name' => 'Suriname', 'code' => 'SR'],
            'gf' => ['name' => 'French Guiana', 'code' => 'GF'],
            'fk' => ['name' => 'Falkland Islands', 'code' => 'FK'],
            'gl' => ['name' => 'Greenland', 'code' => 'GL'],
            'bm' => ['name' => 'Bermuda', 'code' => 'BM'],
            'ky' => ['name' => 'Cayman Islands', 'code' => 'KY'],
            'aw' => ['name' => 'Aruba', 'code' => 'AW'],
            'cw' => ['name' => 'Curacao', 'code' => 'CW'],
            'sx' => ['name' => 'Sint Maarten', 'code' => 'SX'],
            'mc' => ['name' => 'Monaco', 'code' => 'MC'],
            'li' => ['name' => 'Liechtenstein', 'code' => 'LI'],
            'sm' => ['name' => 'San Marino', 'code' => 'SM'],
            'va' => ['name' => 'Vatican City', 'code' => 'VA'],
            'ad' => ['name' => 'Andorra', 'code' => 'AD'],
            'gi' => ['name' => 'Gibraltar', 'code' => 'GI'],
            'fo' => ['name' => 'Faroe Islands', 'code' => 'FO'],
            'je' => ['name' => 'Jersey', 'code' => 'JE'],
            'gg' => ['name' => 'Guernsey', 'code' => 'GG'],
            'im' => ['name' => 'Isle of Man', 'code' => 'IM'],
            'sj' => ['name' => 'Svalbard', 'code' => 'SJ'],
            'bv' => ['name' => 'Bouvet Island', 'code' => 'BV'],
            'hm' => ['name' => 'Heard Island', 'code' => 'HM'],
            'cx' => ['name' => 'Christmas Island', 'code' => 'CX'],
            'cc' => ['name' => 'Cocos Islands', 'code' => 'CC'],
            'nf' => ['name' => 'Norfolk Island', 'code' => 'NF'],
            'pn' => ['name' => 'Pitcairn Islands', 'code' => 'PN'],
            'io' => ['name' => 'British Indian Ocean Territory', 'code' => 'IO'],
            'gs' => ['name' => 'South Georgia', 'code' => 'GS'],
            'sh' => ['name' => 'Saint Helena', 'code' => 'SH'],
            'ac' => ['name' => 'Ascension Island', 'code' => 'AC'],
            'ta' => ['name' => 'Tristan da Cunha', 'code' => 'TA'],
            'tf' => ['name' => 'French Southern Territories', 'code' => 'TF'],
            'aq' => ['name' => 'Antarctica', 'code' => 'AQ'],
            'pm' => ['name' => 'Saint Pierre and Miquelon', 'code' => 'PM'],
            'yt' => ['name' => 'Mayotte', 'code' => 'YT'],
            're' => ['name' => 'Reunion', 'code' => 'RE'],
            'nc' => ['name' => 'New Caledonia', 'code' => 'NC'],
            'pf' => ['name' => 'French Polynesia', 'code' => 'PF'],
            'wf' => ['name' => 'Wallis and Futuna', 'code' => 'WF'],
            'ck' => ['name' => 'Cook Islands', 'code' => 'CK'],
            'nu' => ['name' => 'Niue', 'code' => 'NU'],
            'tk' => ['name' => 'Tokelau', 'code' => 'TK'],
            'vi' => ['name' => 'US Virgin Islands', 'code' => 'VI'],
            // Numeric country IDs (for backwards compatibility)
            '187' => ['name' => 'United States', 'code' => 'US'],
            '16' => ['name' => 'United Kingdom', 'code' => 'GB'],
            '43' => ['name' => 'Germany', 'code' => 'DE'],
            '36' => ['name' => 'Canada', 'code' => 'CA'],
            '175' => ['name' => 'Australia', 'code' => 'AU'],
            '78' => ['name' => 'France', 'code' => 'FR'],
            '1' => ['name' => 'Russia', 'code' => 'RU'],
            '2' => ['name' => 'Ukraine', 'code' => 'UA'],
            '3' => ['name' => 'Kazakhstan', 'code' => 'KZ'],
            '4' => ['name' => 'China', 'code' => 'CN'],
            '5' => ['name' => 'Philippines', 'code' => 'PH'],
            '6' => ['name' => 'Indonesia', 'code' => 'ID'],
            '7' => ['name' => 'Malaysia', 'code' => 'MY'],
            '8' => ['name' => 'Vietnam', 'code' => 'VN'],
            '9' => ['name' => 'Thailand', 'code' => 'TH'],
            '10' => ['name' => 'Brazil', 'code' => 'BR'],
            '11' => ['name' => 'Mexico', 'code' => 'MX'],
            '12' => ['name' => 'Spain', 'code' => 'ES'],
            '13' => ['name' => 'Italy', 'code' => 'IT'],
            '14' => ['name' => 'Poland', 'code' => 'PL'],
            '15' => ['name' => 'Netherlands', 'code' => 'NL'],
            '17' => ['name' => 'Belgium', 'code' => 'BE'],
            '18' => ['name' => 'Sweden', 'code' => 'SE'],
            '19' => ['name' => 'Switzerland', 'code' => 'CH'],
            '20' => ['name' => 'Austria', 'code' => 'AT'],
            '21' => ['name' => 'Denmark', 'code' => 'DK'],
            '22' => ['name' => 'Norway', 'code' => 'NO'],
            '23' => ['name' => 'Finland', 'code' => 'FI'],
            '24' => ['name' => 'Portugal', 'code' => 'PT'],
            '25' => ['name' => 'Greece', 'code' => 'GR'],
            '26' => ['name' => 'Turkey', 'code' => 'TR'],
            '27' => ['name' => 'Saudi Arabia', 'code' => 'SA'],
            '28' => ['name' => 'United Arab Emirates', 'code' => 'AE'],
            '29' => ['name' => 'India', 'code' => 'IN'],
            '30' => ['name' => 'Bangladesh', 'code' => 'BD'],
            '31' => ['name' => 'Pakistan', 'code' => 'PK'],
            '32' => ['name' => 'Nigeria', 'code' => 'NG'],
            '33' => ['name' => 'Egypt', 'code' => 'EG'],
            '34' => ['name' => 'South Africa', 'code' => 'ZA'],
            '35' => ['name' => 'Kenya', 'code' => 'KE'],
            '37' => ['name' => 'Argentina', 'code' => 'AR'],
            '38' => ['name' => 'Chile', 'code' => 'CL'],
            '39' => ['name' => 'Colombia', 'code' => 'CO'],
            '40' => ['name' => 'Peru', 'code' => 'PE'],
            '41' => ['name' => 'Venezuela', 'code' => 'VE'],
            '42' => ['name' => 'Ecuador', 'code' => 'EC'],
            '44' => ['name' => 'Czech Republic', 'code' => 'CZ'],
            '45' => ['name' => 'Romania', 'code' => 'RO'],
            '46' => ['name' => 'Hungary', 'code' => 'HU'],
            '47' => ['name' => 'Bulgaria', 'code' => 'BG'],
            '48' => ['name' => 'Croatia', 'code' => 'HR'],
            '49' => ['name' => 'Serbia', 'code' => 'RS'],
            '50' => ['name' => 'Slovakia', 'code' => 'SK'],
            '51' => ['name' => 'Ireland', 'code' => 'IE'],
            '52' => ['name' => 'New Zealand', 'code' => 'NZ'],
            '53' => ['name' => 'Singapore', 'code' => 'SG'],
            '54' => ['name' => 'Hong Kong', 'code' => 'HK'],
            '55' => ['name' => 'Japan', 'code' => 'JP'],
            '56' => ['name' => 'South Korea', 'code' => 'KR'],
            '57' => ['name' => 'Taiwan', 'code' => 'TW'],
            '58' => ['name' => 'Israel', 'code' => 'IL'],
            '59' => ['name' => 'Iran', 'code' => 'IR'],
            '60' => ['name' => 'Iraq', 'code' => 'IQ'],
            '61' => ['name' => 'Jordan', 'code' => 'JO'],
            '62' => ['name' => 'Kuwait', 'code' => 'KW'],
            '63' => ['name' => 'Lebanon', 'code' => 'LB'],
            '64' => ['name' => 'Oman', 'code' => 'OM'],
            '65' => ['name' => 'Qatar', 'code' => 'QA'],
            '66' => ['name' => 'Morocco', 'code' => 'MA'],
            '67' => ['name' => 'Algeria', 'code' => 'DZ'],
            '68' => ['name' => 'Tunisia', 'code' => 'TN'],
            '69' => ['name' => 'Ghana', 'code' => 'GH'],
            '70' => ['name' => 'Tanzania', 'code' => 'TZ'],
            '71' => ['name' => 'Uganda', 'code' => 'UG'],
            '72' => ['name' => 'Ethiopia', 'code' => 'ET'],
            '73' => ['name' => 'Albania', 'code' => 'AL'],
            '74' => ['name' => 'Bosnia and Herzegovina', 'code' => 'BA'],
            '75' => ['name' => 'Macedonia', 'code' => 'MK'],
            '76' => ['name' => 'Moldova', 'code' => 'MD'],
            '77' => ['name' => 'Belarus', 'code' => 'BY'],
            '79' => ['name' => 'Lithuania', 'code' => 'LT'],
            '80' => ['name' => 'Latvia', 'code' => 'LV'],
            '81' => ['name' => 'Estonia', 'code' => 'EE'],
            '82' => ['name' => 'Slovenia', 'code' => 'SI'],
            '83' => ['name' => 'Luxembourg', 'code' => 'LU'],
            '84' => ['name' => 'Malta', 'code' => 'MT'],
            '85' => ['name' => 'Iceland', 'code' => 'IS'],
            '86' => ['name' => 'Cyprus', 'code' => 'CY'],
            '87' => ['name' => 'Georgia', 'code' => 'GE'],
            '88' => ['name' => 'Armenia', 'code' => 'AM'],
            '89' => ['name' => 'Azerbaijan', 'code' => 'AZ'],
            '90' => ['name' => 'Uzbekistan', 'code' => 'UZ'],
            '91' => ['name' => 'Kyrgyzstan', 'code' => 'KG'],
            '92' => ['name' => 'Tajikistan', 'code' => 'TJ'],
            '93' => ['name' => 'Turkmenistan', 'code' => 'TM'],
            '94' => ['name' => 'Mongolia', 'code' => 'MN'],
            '95' => ['name' => 'Nepal', 'code' => 'NP'],
            '96' => ['name' => 'Sri Lanka', 'code' => 'LK'],
            '97' => ['name' => 'Myanmar', 'code' => 'MM'],
            '98' => ['name' => 'Cambodia', 'code' => 'KH'],
            '99' => ['name' => 'Laos', 'code' => 'LA'],
            '100' => ['name' => 'Maldives', 'code' => 'MV'],
            '101' => ['name' => 'Bhutan', 'code' => 'BT'],
            '102' => ['name' => 'Brunei', 'code' => 'BN'],
            '103' => ['name' => 'East Timor', 'code' => 'TL'],
            '104' => ['name' => 'Fiji', 'code' => 'FJ'],
            '105' => ['name' => 'Papua New Guinea', 'code' => 'PG'],
            '106' => ['name' => 'Solomon Islands', 'code' => 'SB'],
            '107' => ['name' => 'Vanuatu', 'code' => 'VU'],
            '108' => ['name' => 'Samoa', 'code' => 'WS'],
            '109' => ['name' => 'Tonga', 'code' => 'TO'],
            '110' => ['name' => 'Palau', 'code' => 'PW'],
            '111' => ['name' => 'Micronesia', 'code' => 'FM'],
            '112' => ['name' => 'Marshall Islands', 'code' => 'MH'],
            '113' => ['name' => 'Nauru', 'code' => 'NR'],
            '114' => ['name' => 'Kiribati', 'code' => 'KI'],
            '115' => ['name' => 'Tuvalu', 'code' => 'TV'],
            '116' => ['name' => 'Northern Mariana Islands', 'code' => 'MP'],
            '117' => ['name' => 'Guam', 'code' => 'GU'],
            '118' => ['name' => 'American Samoa', 'code' => 'AS'],
            '119' => ['name' => 'Puerto Rico', 'code' => 'PR'],
            '120' => ['name' => 'Dominican Republic', 'code' => 'DO'],
            '121' => ['name' => 'Jamaica', 'code' => 'JM'],
            '122' => ['name' => 'Haiti', 'code' => 'HT'],
            '123' => ['name' => 'Trinidad and Tobago', 'code' => 'TT'],
            '124' => ['name' => 'Barbados', 'code' => 'BB'],
            '125' => ['name' => 'Bahamas', 'code' => 'BS'],
            '126' => ['name' => 'Belize', 'code' => 'BZ'],
            '127' => ['name' => 'Costa Rica', 'code' => 'CR'],
            '128' => ['name' => 'Panama', 'code' => 'PA'],
            '129' => ['name' => 'Nicaragua', 'code' => 'NI'],
            '130' => ['name' => 'Honduras', 'code' => 'HN'],
            '131' => ['name' => 'El Salvador', 'code' => 'SV'],
            '132' => ['name' => 'Guatemala', 'code' => 'GT'],
            '133' => ['name' => 'Cuba', 'code' => 'CU'],
            '134' => ['name' => 'Paraguay', 'code' => 'PY'],
            '135' => ['name' => 'Uruguay', 'code' => 'UY'],
            '136' => ['name' => 'Bolivia', 'code' => 'BO'],
            '137' => ['name' => 'Guyana', 'code' => 'GY'],
            '138' => ['name' => 'Suriname', 'code' => 'SR'],
            '139' => ['name' => 'French Guiana', 'code' => 'GF'],
            '140' => ['name' => 'Falkland Islands', 'code' => 'FK'],
            '141' => ['name' => 'Greenland', 'code' => 'GL'],
            '142' => ['name' => 'Bermuda', 'code' => 'BM'],
            '143' => ['name' => 'Cayman Islands', 'code' => 'KY'],
            '144' => ['name' => 'Aruba', 'code' => 'AW'],
            '145' => ['name' => 'Curacao', 'code' => 'CW'],
            '146' => ['name' => 'Sint Maarten', 'code' => 'SX'],
            '147' => ['name' => 'Monaco', 'code' => 'MC'],
            '148' => ['name' => 'Liechtenstein', 'code' => 'LI'],
            '149' => ['name' => 'San Marino', 'code' => 'SM'],
            '150' => ['name' => 'Vatican City', 'code' => 'VA'],
            '151' => ['name' => 'Andorra', 'code' => 'AD'],
            '152' => ['name' => 'Gibraltar', 'code' => 'GI'],
            '153' => ['name' => 'Faroe Islands', 'code' => 'FO'],
            '154' => ['name' => 'Jersey', 'code' => 'JE'],
            '155' => ['name' => 'Guernsey', 'code' => 'GG'],
            '156' => ['name' => 'Isle of Man', 'code' => 'IM'],
            '157' => ['name' => 'Svalbard', 'code' => 'SJ'],
            '158' => ['name' => 'Jan Mayen', 'code' => 'SJ'],
            '159' => ['name' => 'Bouvet Island', 'code' => 'BV'],
            '160' => ['name' => 'Heard Island', 'code' => 'HM'],
            '161' => ['name' => 'Christmas Island', 'code' => 'CX'],
            '162' => ['name' => 'Cocos Islands', 'code' => 'CC'],
            '163' => ['name' => 'Norfolk Island', 'code' => 'NF'],
            '164' => ['name' => 'Pitcairn Islands', 'code' => 'PN'],
            '165' => ['name' => 'British Indian Ocean Territory', 'code' => 'IO'],
            '166' => ['name' => 'South Georgia', 'code' => 'GS'],
            '167' => ['name' => 'Saint Helena', 'code' => 'SH'],
            '168' => ['name' => 'Ascension Island', 'code' => 'AC'],
            '169' => ['name' => 'Tristan da Cunha', 'code' => 'TA'],
            '170' => ['name' => 'French Southern Territories', 'code' => 'TF'],
            '171' => ['name' => 'Antarctica', 'code' => 'AQ'],
            '172' => ['name' => 'Saint Pierre and Miquelon', 'code' => 'PM'],
            '173' => ['name' => 'Mayotte', 'code' => 'YT'],
            '174' => ['name' => 'Reunion', 'code' => 'RE'],
            '176' => ['name' => 'New Caledonia', 'code' => 'NC'],
            '177' => ['name' => 'French Polynesia', 'code' => 'PF'],
            '178' => ['name' => 'Wallis and Futuna', 'code' => 'WF'],
            '179' => ['name' => 'Cook Islands', 'code' => 'CK'],
            '180' => ['name' => 'Niue', 'code' => 'NU'],
            '181' => ['name' => 'Tokelau', 'code' => 'TK'],
            '182' => ['name' => 'American Samoa', 'code' => 'AS'],
            '183' => ['name' => 'Guam', 'code' => 'GU'],
            '184' => ['name' => 'Northern Mariana Islands', 'code' => 'MP'],
            '185' => ['name' => 'Puerto Rico', 'code' => 'PR'],
            '186' => ['name' => 'US Virgin Islands', 'code' => 'VI'],
        ];
    }

    public function getPricing(array $params = []): array
    {
        try {
            $actionParams = [];
            if (!empty($params['service'])) $actionParams['service'] = $params['service'];
            if (!empty($params['country'])) $actionParams['country'] = $params['country'];
            $resp = $this->makeRequest('getPrices', $actionParams);
            
            Log::info('TigerSMS getPricing response', [
                'params' => $actionParams,
                'has_success' => isset($resp['success']),
                'success_value' => $resp['success'] ?? null,
                'has_data' => isset($resp['data']),
                'has_prices' => isset($resp['prices']),
                'has_message' => isset($resp['message']),
                'message' => $resp['message'] ?? null,
                'is_array' => is_array($resp),
                'resp_keys' => is_array($resp) ? array_keys($resp) : [],
            ]);
            
            // Check for error responses first
            if (isset($resp['success']) && $resp['success'] === false) {
                Log::warning('TigerSMS getPricing returned error', ['resp' => $resp]);
                // Return empty array so getServices can handle it, but log the error
                return [];
            }
            
            // If we got a response but no data, log the full response structure
            if (isset($resp['success']) && $resp['success'] && empty($resp['data'])) {
                Log::warning('TigerSMS getPricing: success=true but no data', [
                    'resp_keys' => array_keys($resp),
                    'full_resp' => $resp,
                ]);
            }
            
            if (isset($resp['success']) && $resp['success'] && isset($resp['data'])) {
                return $resp['data'];
            }
            // Some responses might be direct data array
            if (isset($resp['prices']) && is_array($resp['prices'])) return $resp['prices'];
            if (isset($resp['raw']) && is_array($resp['raw'])) return $resp['raw'];
            // If resp is already the matrix (direct pricing data)
            if (is_array($resp) && !isset($resp['success'])) {
                Log::info('TigerSMS returning direct matrix', ['top_keys' => array_slice(array_keys($resp), 0, 5)]);
                return $resp;
            }
            Log::warning('TigerSMS getPricing returned empty', [
                'resp' => $resp,
                'resp_type' => gettype($resp),
                'resp_keys' => is_array($resp) ? array_keys($resp) : 'not array',
            ]);
            return [];
        } catch (\Throwable $e) {
            Log::error('TigerSMS pricing error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [];
        }
    }

    /**
     * Get available countries for a specific service
     */
    public function getAvailableCountriesForService(string $serviceId): array
    {
        try {
            Log::info('TigerSMS getAvailableCountriesForService called', ['service_id' => $serviceId]);
            
            // Get full pricing matrix (all services, all countries)
            // TigerSMS doesn't support filtering by service in getPricing
            $pricing = $this->getPricing();
            
            Log::info('TigerSMS getAvailableCountriesForService pricing response', [
                'service_id' => $serviceId,
                'has_pricing' => !empty($pricing),
                'pricing_type' => gettype($pricing),
                'pricing_keys' => is_array($pricing) ? array_slice(array_keys($pricing), 0, 5) : [],
            ]);
            
            if (!is_array($pricing) || empty($pricing)) {
                Log::warning('TigerSMS getAvailableCountriesForService: No pricing data', ['service_id' => $serviceId]);
                return ['success' => false, 'error' => 'No pricing data available for this service'];
            }
            
            // Extract country IDs for the specific service
            // Pricing structure: { serviceCode: { countryId: { price, count } } }
            $countryIds = [];
            
            // Check if the service exists in pricing data
            if (isset($pricing[$serviceId]) && is_array($pricing[$serviceId])) {
                foreach ($pricing[$serviceId] as $countryId => $info) {
                    if (is_array($info)) {
                        // Only include countries that have actual pricing data (not empty)
                        $hasPrice = false;
                        foreach (['cost', 'price', 'amount', 'value'] as $field) {
                            if (isset($info[$field]) && is_numeric($info[$field]) && (float)$info[$field] > 0) {
                                $hasPrice = true;
                                break;
                            }
                        }
                        if ($hasPrice) {
                            $countryIds[(string)$countryId] = true;
                        }
                    }
                }
            }
            
            Log::info('TigerSMS getAvailableCountriesForService extracted countries', [
                'service_id' => $serviceId,
                'country_count' => count($countryIds),
                'sample_countries' => array_slice(array_keys($countryIds), 0, 10),
            ]);
            
            if (empty($countryIds)) {
                Log::warning('TigerSMS getAvailableCountriesForService: No countries found for service', ['service_id' => $serviceId]);
                return [
                    'success' => true,
                    'countries' => [],
                    'message' => 'No countries available for this service',
                ];
            }
            
            // Map to country info - use TigerSMS codes directly
            // TigerSMS uses text codes like "dz", "ar", "am" - these are the actual IDs
            $availableCountries = [];
            foreach (array_keys($countryIds) as $tigerCode) {
                $codeStr = strtolower((string)$tigerCode);
                
                // Try to match TigerSMS code to country name via ISO code mapping
                $countryMap = $this->getCountryMap();
                $matchedCountry = null;
                
                // First try: Match by ISO code (many TigerSMS codes match ISO codes)
                foreach ($countryMap as $numericId => $info) {
                    if (is_array($info) && isset($info['code'])) {
                        $isoCode = strtolower($info['code']);
                        if ($isoCode === $codeStr) {
                            $matchedCountry = [
                                'id' => $codeStr, // Use TigerSMS code as ID
                                'name' => $info['name'] ?? "Country {$codeStr}",
                                'code' => $info['code'] ?? strtoupper($codeStr),
                            ];
                            break;
                        }
                    }
                }
                
                // If not matched, use generic name
                if (!$matchedCountry) {
                    // Try to guess from common patterns
                    $name = ucfirst($codeStr);
                    if (strlen($codeStr) === 2) {
                        // Probably an ISO code we don't have mapped
                        $name = "Country " . strtoupper($codeStr);
                    }
                    $matchedCountry = [
                        'id' => $codeStr,
                        'name' => $name,
                        'code' => strlen($codeStr) === 2 ? strtoupper($codeStr) : null,
                    ];
                }
                
                $availableCountries[] = $matchedCountry;
            }
            
            // Sort by name
            usort($availableCountries, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
            
            Log::info('TigerSMS getAvailableCountriesForService result', [
                'service_id' => $serviceId,
                'countries_count' => count($availableCountries),
                'sample' => array_slice($availableCountries, 0, 3),
            ]);
            
            return [
                'success' => true,
                'countries' => $availableCountries,
            ];
        } catch (\Throwable $e) {
            Log::error('TigerSMS getAvailableCountriesForService error: ' . $e->getMessage(), [
                'service_id' => $serviceId,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function requestNumber(string $service, string $country = null, array $options = []): array
    {
        try {
            // TigerSMS Flow:
            // 1. Service: Use numeric ID directly (e.g., "22" for WhatsApp)
            // 2. Country: Use TigerSMS text code from pricing data (e.g., "dz", "ar", "am")
            // 3. No conversion needed - use codes as-is from getAvailableCountriesForService
            
            $serviceCode = trim((string)$service);
            $params = [
                'service' => $serviceCode,
            ];

            // Handle country parameter
            if (!empty($country)) {
                $countryStr = trim((string)$country);
                
                // TigerSMS uses text country codes (e.g., "dz", "ar", "am")
                // These come directly from getAvailableCountriesForService
                // Use as-is, no conversion needed
                $params['country'] = strtolower($countryStr);
                
                Log::info('TigerSMS requestNumber', [
                    'service' => $serviceCode,
                    'country' => $params['country'],
                    'params' => $params,
                ]);
            } else {
                Log::info('TigerSMS requestNumber: no country specified', [
                    'service' => $serviceCode,
                ]);
            }
            
            // Add max price if specified (in RUB)
            if (isset($options['max_price'])) {
                $params['maxPrice'] = (string)$options['max_price'];
            }
            
            // Make API request
            $resp = $this->makeRequest('getNumber', $params);
            
            // Handle successful response
            if (isset($resp['success']) && $resp['success'] && (isset($resp['id']) || isset($resp['number']))) {
                return [
                    'success' => true,
                    'order_id' => $resp['id'] ?? null,
                    'number' => $resp['number'] ?? null,
                    'service' => $service,
                    'country' => $country,
                    'provider' => $this->getName(),
                ];
            }
            
            // Handle ACCESS_READY format: ACCESS_READY:id:number
            if (isset($resp['status_text']) && strpos($resp['status_text'], 'ACCESS_') === 0) {
                $parts = explode(':', $resp['status_text']);
                return [
                    'success' => true,
                    'order_id' => $parts[1] ?? null,
                    'number' => $parts[2] ?? null,
                    'service' => $service,
                    'country' => $country,
                    'provider' => $this->getName(),
                ];
            }
            
            // Handle errors
            $errorMsg = $resp['message'] ?? 'Failed to request number';
            
            // Improve error messages
            if ($errorMsg === 'BAD_SERVICE' || strpos($errorMsg, 'BAD_SERVICE') !== false) {
                $errorMsg = "Service '{$serviceCode}' is not available. Please select a different service.";
            } elseif ($errorMsg === 'BAD_COUNTRY' || strpos($errorMsg, 'BAD_COUNTRY') !== false) {
                if (!empty($country)) {
                    $errorMsg = "Country '{$country}' is not available for service '{$serviceCode}'. Please select a country from the filtered list.";
                } else {
                    $errorMsg = "Country is required for this service. Please select a country.";
                }
            }
            
            Log::warning('TigerSMS requestNumber failed', [
                'service' => $serviceCode,
                'country' => $country ?? 'none',
                'params' => $params,
                'response' => $resp,
                'error' => $errorMsg,
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('TigerSMS requestNumber error: ' . $e->getMessage(), [
                'service' => $service ?? null,
                'country' => $country ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getMessages(string $orderId): array
    {
        try {
            $resp = $this->makeRequest('getStatus', ['id' => $orderId]);
            if (isset($resp['success']) && $resp['success'] && isset($resp['status'])) {
                $out = [
                    'success' => true,
                    'status' => $resp['status'],
                    'messages' => [],
                ];
                if ($resp['status'] === 'STATUS_OK' && isset($resp['code'])) {
                    $out['messages'][] = [
                        'code' => $resp['code'],
                        'text' => $resp['text'] ?? null,
                        'received_at' => now()->toDateTimeString(),
                    ];
                }
                return $out;
            }
            // Text response parser
            if (isset($resp['status_text'])) {
                $txt = $resp['status_text'];
                if (strpos($txt, 'STATUS_OK:') === 0) {
                    $code = substr($txt, 10);
                    return [
                        'success' => true,
                        'status' => 'STATUS_OK',
                        'messages' => [[ 'code' => trim($code), 'text' => null, 'received_at' => now()->toDateTimeString() ]],
                        'sms_code' => trim($code),
                    ];
                }
                return [ 'success' => true, 'status' => $txt, 'messages' => [] ];
            }
            return [ 'success' => false, 'error' => $resp['message'] ?? 'Failed to get status' ];
        } catch (\Throwable $e) {
            Log::error('TigerSMS getMessages error: ' . $e->getMessage());
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }
    }

    private function isPopularService(string $name): bool
    {
        $popular = ['whatsapp','facebook','google','instagram','telegram','twitter','tiktok','snapchat','amazon','microsoft','apple','x'];
        foreach ($popular as $p) {
            if (stripos($name, $p) !== false) return true;
        }
        return false;
    }
    
    /**
     * Convert service ID (numeric or text) to TigerSMS text code
     * TigerSMS uses text codes like "wa", "qu", "fb" for requests
     */
    private function getServiceCode(string $serviceId): string
    {
        $serviceId = trim((string)$serviceId);
        
        // If already a text code (not numeric), return as-is
        if (!is_numeric($serviceId)) {
            return strtolower($serviceId);
        }
        
        // Map numeric IDs to TigerSMS text codes
        $numericToTextMap = [
            '11' => 'wa',   // WhatsApp
            '30' => 'qu',   // Airbnb
            '13' => 'fb',   // Facebook
            '14' => 'go',   // Google
            '15' => 'ig',   // Instagram
            '16' => 'tw',   // Twitter
            '17' => 'lf',   // TikTok/Douyin
            '18' => 'fu',   // Snapchat
            '19' => 'yo',   // Amazon
            '20' => 'mm',   // Microsoft
            '21' => 'wx',   // Apple
            '22' => 'wa',   // WhatsApp (alternative)
            '23' => 'ds',   // Discord
            '24' => 'tn',   // LinkedIn
            '25' => 'lf',   // Pinterest (check actual code)
            '26' => 'nf',   // Reddit
            '27' => 'alj',  // Spotify
            '28' => 'nf',   // Netflix
            '29' => 'ub',   // Uber
            '31' => 'ts',   // PayPal
            '32' => 'dh',   // eBay
            '33' => 'hx',   // AliExpress
            '34' => 'ab',   // Alibaba
            '35' => 'wb',   // WeChat
            '36' => 'qq',   // QQ
            '37' => 'li',   // Baidu
            '38' => 'vk',   // VKontakte
            '39' => 'ok',   // Odnoklassniki
            '40' => 'ma',   // Mail.ru
            '41' => 'ya',   // Yandex
            '42' => 'av',   // Avito
            '43' => 'sn',   // OLX
            '44' => 'jg',   // Grab
            '45' => 'ni',   // Gojek
            '46' => 'ka',   // Shopee
            '47' => 'dl',   // Lazada
            '48' => 'xd',   // Tokopedia
            '49' => 'kh',   // Bukalapak
            '50' => 'me',   // LINE
            '51' => 'kt',   // KakaoTalk
            '52' => 'nv',   // Naver
            '53' => 'kf',   // Weibo
            '54' => 'lf',   // Douyin
            '55' => 'oi',   // Tinder
            '56' => 'mo',   // Bumble
            '57' => 'df',   // Happn
            '58' => 'vm',   // OkCupid
            '59' => 'yw',   // Grindr
            '60' => 'mb',   // Bing
            '61' => 'mb',   // Yahoo
            '62' => 'dp',   // Outlook
            '63' => 'dp',   // Hotmail
            '64' => 'go',   // Gmail
            '65' => 'pm',   // AOL
            '66' => 'iq',   // ICQ
            '67' => 'vi',   // Viber
            '68' => 'im',   // IMO
            '69' => 'fd',   // MeetMe
            '70' => 'fd',   // Mamba
            '71' => 'qv',   // Badoo
            // Add more mappings as needed
        ];
        
        return $numericToTextMap[$serviceId] ?? $serviceId; // Fallback to original if not mapped
    }

    private function makeRequest(string $action, array $params = []): array
    {
        // Try multiple possible endpoints
        $possibleEndpoints = [
            rtrim($this->baseUrl, '/') . '/stubs/handler_api.php',
            rtrim($this->baseUrl, '/') . '/handler_api.php',
            rtrim($this->baseUrl, '/') . '/api.php',
            'https://tiger-sms.com/stubs/handler_api.php',
            'https://tiger-sms.com/handler_api.php',
        ];
        
        $requestParams = array_merge(['api_key' => $this->apiKey, 'action' => $action], $params);
        $query = http_build_query($requestParams);
        
        $lastError = null;
        foreach ($possibleEndpoints as $base) {
            $url = $base . '?' . $query;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Shorter timeout for testing
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'User-Agent: BiggestLogs/1.0',
            ]);
            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            Log::info('TigerSMS HTTP call attempt', [
                'action' => $action,
                'url' => $url,
                'http' => $httpCode,
                'body_length' => strlen((string)$body),
                'preview' => is_string($body) ? substr($body, 0, 200) : null,
            ]);
            
            if ($err) {
                $lastError = $err;
                continue; // Try next endpoint
            }
            
            if ($httpCode === 200) {
                // Success! This endpoint works
                Log::info('TigerSMS working endpoint found', ['endpoint' => $base]);
                return $this->parseResponse((string)$body, $action);
            }
            
            if ($httpCode !== 404) {
                // Not 404, might be a different error (400, 401, etc.) - return it
                return ['success' => false, 'message' => 'HTTP ' . $httpCode . ': ' . substr((string)$body, 0, 120)];
            }
            
            // 404, try next endpoint
            $lastError = 'HTTP 404';
        }
        
        // All endpoints failed
        Log::error('TigerSMS all endpoints failed', ['last_error' => $lastError]);
        return ['success' => false, 'message' => 'All API endpoints returned 404. Please check API documentation.'];
    }

    private function parseResponse(string $body, string $action): array
    {
        $body = trim($body);
        
        // Handle getPrices separately - TigerSMS returns direct pricing matrix
        if ($action === 'getPrices') {
            // Check for text error responses first (BAD_KEY, BAD_ACTION, etc.)
            if (strpos($body, 'BAD_') === 0) {
                Log::error('TigerSMS getPrices error response', ['body' => $body]);
                return [ 'success' => false, 'message' => $body ];
            }
            
            // Try JSON first
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                // Check if it's the pricing matrix format {service: {country: {price, count}}}
                if ($this->isPricingMatrix($json)) {
                    Log::info('TigerSMS getPrices: Valid pricing matrix received', [
                        'services_count' => count($json),
                        'sample_service' => array_keys($json)[0] ?? null,
                    ]);
                    return [ 'success' => true, 'data' => $json ];
                }
                // If wrapped in success/data structure
                if (isset($json['data']) && is_array($json['data']) && $this->isPricingMatrix($json['data'])) {
                    return [ 'success' => true, 'data' => $json['data'] ];
                }
                // If it's the matrix itself (even if not detected as matrix)
                if (!empty($json)) {
                    Log::info('TigerSMS getPrices: Returning JSON data (not recognized as matrix)', [
                        'json_keys' => array_keys($json),
                        'first_key_type' => isset($json[array_key_first($json)]) ? gettype($json[array_key_first($json)]) : null,
                    ]);
                    return [ 'success' => true, 'data' => $json ];
                }
            }
            
            // Try sanitizing non-standard JSON
            $sanitized = preg_replace('/([{,]\s*)(\w+)(\s*):/', '$1"$2"$3:', $body); // Quote unquoted keys
            $sanitized = str_replace("'", '"', $sanitized); // Single to double quotes
            $json2 = json_decode($sanitized, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json2) && $this->isPricingMatrix($json2)) {
                Log::info('TigerSMS getPrices: Sanitized JSON parsed successfully');
                return [ 'success' => true, 'data' => $json2 ];
            }
            
            // Log raw response for debugging - include full body if short
            Log::warning('TigerSMS getPrices: Could not parse response', [
                'body_length' => strlen($body),
                'body_preview' => strlen($body) > 500 ? substr($body, 0, 500) : $body,
                'json_error' => json_last_error_msg(),
            ]);
            return [ 'success' => false, 'message' => 'Invalid pricing data format: ' . substr($body, 0, 200), 'raw' => $body ];
        }
        
        // Try JSON for other actions
        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            if (!isset($json['success'])) $json['success'] = true;
            return $json;
        }
        
        // Text parsing for other actions
        if ($action === 'getBalance') {
            if (is_numeric($body)) return [ 'success' => true, 'balance' => (float)$body ];
            if (strpos($body, 'ACCESS_BALANCE:') === 0) {
                return [ 'success' => true, 'balance' => (float)substr($body, 15) ];
            }
            if (strpos($body, 'BAD_') === 0) return [ 'success' => false, 'message' => $body ];
        }
        if ($action === 'getNumber') {
            // ACCESS_READY:id:number
            if (strpos($body, 'ACCESS_READY') === 0) return [ 'success' => true, 'status_text' => $body ];
            if (strpos($body, 'BAD_') === 0) return [ 'success' => false, 'message' => $body ];
        }
        if ($action === 'getStatus') {
            if (strpos($body, 'STATUS_') === 0) return [ 'success' => true, 'status_text' => $body ];
            if (strpos($body, 'BAD_') === 0) return [ 'success' => false, 'message' => $body ];
        }
        return [ 'success' => false, 'message' => $body ];
    }
    
    private function isPricingMatrix($data): bool
    {
        if (!is_array($data) || empty($data)) return false;
        // Check if structure is {service: {country: {...}}}
        $firstKey = array_key_first($data);
        if ($firstKey === null) return false;
        $firstValue = $data[$firstKey];
        if (!is_array($firstValue)) return false;
        // Check if countries are nested
        $countryKey = array_key_first($firstValue);
        if ($countryKey === null) return false;
        $countryValue = $firstValue[$countryKey];
        // Should be array with price/count or similar
        return is_array($countryValue);
    }
}



