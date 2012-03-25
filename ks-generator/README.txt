Kickstart generator
===================
Initial version 20100226

Generates kickstart script for CentOS
and other RH compatible Linux OS

Current version only supports saving scripts to files
and does not allows to edit existing script 
(actually to create new script based on new one).

Kickstart script allows unattended, automatic
install of CentOS.

Kickstart generator home:
http://ks.sdot.ru/

Discussion page:
http://huksley.sdot.ru/110/kickstart-generator.html

Kickstart introduction:
http://www.centos.org/docs/5/html/5.2/Installation_Guide/s1-kickstart2-howuse.html

Changes
=======

20100226 Added more languages and keyboard selection
20091025 Small fixes to typos done in initial release
20091021 Initial release

Installation
============

Your webserver (Apache?) must support url rewriting.

Change $ksurl at the top of the ks.php
to point to absolute url of this folder.

Modify .htaccess if you have installed it not in the root 
of webserver but in some directory, e.g. http://localhost/ks/

Make scripts directory world (actually apache) writeable.

License
=======
GNU GENERAL PUBLIC LICENSE
Version 2
See included license.txt

Update from Edy
1. change the ks.php $ksurl. should no need to setup manually
2. further work: add repo/other plugins for the server setup, such as AMP package. write log of installation
