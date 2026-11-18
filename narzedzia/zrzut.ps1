# Zrzut strony przegladarka Edge w trybie headless (do sprawozdania). Plik tylko ASCII (PowerShell 5.1).
# Uzycie: powershell -File narzedzia\zrzut.ps1 -Url http://beanbox.local/sklep/ -Plik sprawozdanie\zrzuty\sklep.png [-Szer 1366] [-Wys 1800]
param([string]$Url, [string]$Plik, [int]$Szer = 1366, [int]$Wys = 1800)
$edge = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
$dir = "$env:TEMP\bbshot"; New-Item -ItemType Directory -Force $dir | Out-Null
$tmp = "$dir\shot.png"; if (Test-Path $tmp) { Remove-Item $tmp }
$p = Start-Process $edge -ArgumentList "--headless=new","--disable-gpu","--hide-scrollbars","--no-first-run","--user-data-dir=$dir\prof","--window-size=$Szer,$Wys","--virtual-time-budget=8000","--screenshot=$tmp",$Url -PassThru -Wait -WindowStyle Hidden
if (Test-Path $tmp) { New-Item -ItemType Directory -Force (Split-Path $Plik) | Out-Null; Copy-Item $tmp $Plik -Force; "OK $Plik" } else { "BLAD zrzutu (exit $($p.ExitCode))" }
