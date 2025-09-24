php artisan tinker
exit
php artisan make:model User
php artisan make:model User --force
php artisan make:model Attendance
php artisan make:model AttendanceLog
php artisan make:model StampCorrectionRequest
php artisan make:request Auth/RegisterRequest
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/AttendanceStampRequest
php artisan make:request Auth/CorrectionRequest
php artisan make:request User/AttendanceStampRequest
php artisan make:request User/CorrectionRequest
php artisan make:request Admin/RequestApprovalRequest
php artisan make:request Admin/AdminLoginRequest
exit
php artisan make:controller コン RegisterController
php artisan make:controller RegisterController
php artisan make:controller LoginController
php artisan make:controller AttendanceController
php artisan make:controller Auth\RegisterController
php artisan make:controller Auth\\RegisterController
php artisan make:controller Auth\\LoginController
php artisan make:controller User\\AttendanceController
php artisan make:controller User\\RequestController
php artisan make:controller Admin\\Auth\\LoginController
php artisan make:controller Admin\\AttendanceController
php artisan make:controller Admin\\UserController
php artisan make:controller Admin\\RequestController
exit
php artisan make:request User/AttendanceCorrectionRequest
php artisan make:migration create_break_logs_table
php artisan make:model BreakLog
php artisan migrate:fresh
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan migrate:fresh
php artisan migrate:fresh
php artisan migrate:fresh
php artisan migrate:fresh
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan make:migration create_correction_break_logs_table
php artisan make:model CorrectionBreakLog
php artisan migrate:fresh
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
exit
php artisan tinker
exit
php artisan route:list | grep attendances
exit
php artisan tinker
exit
php artisan make:seeder AttendancesTableSeeder
php artisan make:seeder UsersTableSeeder
php artisan migrate:fresh --seed
php artisan migrate:fresh --seed
php artisan migrate:fresh --seed
php artisan migrate:fresh --seed
php artisan migrate:fresh --seed
exit
composer require --dev laravel/dusk
php artisan dusk:install
exit
cp .env .env.testing
php artisan key:generate --env=testing
php artisan config:clear
php artisan migrate --env=testing
php artisan make:test HelloTest
exit
vendor/bin/phpunit tests/Feature/HelloTest.php
vendor/bin/phpunit tests/Feature/HelloTest.php
vendor/bin/phpunit tests/Feature/HelloTest.php
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
vendor/bin/phpunit tests/Feature/HelloTest.php
vendor/bin/phpunit tests/Feature/HelloTest.php
vendor/bin/phpunit tests/Feature/HelloTest.php
php artisan test --filter=UserRegistrationTest
exit
php artisan test
php artisan test
php artisan test tests/Feature/AttendancePunchTest
php artisan test tests/Feature/AttendancePunchTest.php
php artisan test tests/Feature/AttendancePunchTest.php
php artisan test tests/Feature/AttendancePunchTest.php
php artisan test tests/Feature/AttendanceStatusTest.php
php artisan test tests/Feature/AttendanceStatusTest.php
php artisan test tests/Feature/AttendanceStatusTest.php
php artisan test tests/Feature/AttendanceStatusTest.php
php artisan test tests/Feature/AttendanceStatusTest.php
php artisan test
exit
