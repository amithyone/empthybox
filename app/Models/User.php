<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin_hash',
        'phone',
        'is_admin',
        'is_active',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    public function smsOrders()
    {
        return $this->hasMany(SmsOrder::class);
    }

    public function manualPayments()
    {
        return $this->hasMany(ManualPayment::class);
    }
}
