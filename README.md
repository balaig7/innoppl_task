Follow these things after cloning the project:
-----------------------------------------------

1)Composer update.
2)php artisan migrate to import database tables.
2)Run php artisan db:seed to create an admin user.
3)To create a customer just click on register and create a new customer.
4)I configured "Mail trap" as smtp host. currently in sandbox mode. So that you didn't get message to your email. You can login into mail trap and see messages in mail trap inbox. If you want to get messages in real time configure your own smtp host.

