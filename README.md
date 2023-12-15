Platform: PHP 8.1.2-1ubuntu2.14 (cli) (built: Aug 18 2023 11:41:11)

The tool is thoroughly documented with class, method, var and line comments throughout.

SETUP;
    Unzip into web root directory
	
    Set ownership of new directory "Claromentis" to the same as the apache2 user (usually: chown -R www-data:www-data)
	
    Set filemode of uploads and downloads directories (usually: chown -R 777 uploads & chown -R 777 downloads)
	
    visit http://localhost/Claromentis/index.php

Hello there,
Brendan here, I submit to you the unnecessarily over engineered CSV importer / calculator / exporter tool for consideration.

I took the "feel free to over-engineer to show off." and ran with it as I was checking out the finer points of PHP 8.

I realise that this tool could have been written in a single class with a few method calls but I've tried to squeeze in a little of everything with the time I had to allot to this project.

So here we are with;
A final child class "Reporter" which handles file parsing, calculations and redirects
which extends the parent abstract "Filer" class which implements the "FilerInterface" interface to handle file upload, move, download, validation and relative / absolute file path resolutions.

Things I ran out of time for;

    Login / Auth interface
        For this I would usually have saved a hashed password salted with a secret in the database at account create time. Then, at login time the supplied password string hashed with a secret would be compared to the one stored in the database (obviously this prevents the need to actually store the users actual password in a DB).

        So, to answer the multiple private user problem without login, expense reports offered for download are appended with a uniqid() eg. “2023-12-15-16-31-23_657c633b089bc.csv”. This way users cannot guess the filename of other user generated files.

    Custom exception handlers
        As demonstrated in the code I am throwing annotated exceptions, but these are catched directly by PHP’s own exception handler, if I had more time I would have registered a custome exception handler with set_exception_handler() and dealt with them more gracefully.

    Centralised config file for paths, VAT rate etc

Things I would have done differently;

    The renderer "index.php"
        I am more used to working with Laravel / Routes / Blade these days so this felt a little hacked together, if I had more time I would have implemented a rudimentary router that excepted the HTTP operations with some sort of simple templater mechanism instead of just using PHP global $_SESSION to pass data around the application.

    Autoloading - there is none
