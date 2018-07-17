<?php
//reset database
exec('mysql -u root --password="" < db/test_db_setup.sql');
//load data
exec('mysql -u root --password="" < db/test_data.sql');
?>
