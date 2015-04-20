## How to install

### Step 1.

Clone ContrA github repository to a desired server using the following command:  
`git clone https://github.com/N4SJAMK/IIZP2010G5.git`

### Step 2.

Install the Mongo driver for PHP and other necessary libraries:

`sudo apt-get install php5-dev php5-cli php-pear php5-mcrypt`

`sudo pecl install mongo`

Add the following lines to your php.ini file:

`extension=mongo.so`

`extension=mcrypt.so`

### Step 3.

Open “config.php” file with a text editor and change the settings to your liking. If your database has a username and a password set, delete the backslashes from `DB_USER` and `DB_PASS` and change the credentials to match your database's credentials.

### Step 4.

Go to the ContrA website using a modern browser.

### Step 5.

???

### Step 6.

Profit

***
[Back to Wiki](https://github.com/N4SJAMK/IIZP2010G5/wiki)
