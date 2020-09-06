@echo off
set build_dir=build
echo Build PATE to %build_dir%

if not exist %build_dir% mkdir %build_dir%
copy *.php %build_dir%
copy *.css %build_dir%
copy *.js %build_dir%
copy *.ini %build_dir%

