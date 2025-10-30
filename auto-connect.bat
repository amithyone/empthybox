@echo off
echo Connecting to Contabo Server...
echo.

REM Accept the SSH key automatically
echo y | plink -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18 exit

REM Now connect interactively
echo.
echo Opening interactive SSH session...
echo Press Ctrl+D or type 'exit' to disconnect
echo.
plink -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18

pause

