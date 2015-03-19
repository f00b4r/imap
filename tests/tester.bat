@echo off
%CD%\..\vendor\bin\tester.bat %CD%\Imap -s -j 40 -log %CD%\imap.log %*
rmdir %CD%\tmp /Q /S
