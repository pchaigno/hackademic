# hackademic
#
# VERSION               1.0
FROM debian:stable
MAINTAINER Paul Chaignon <paul.chaignon@gmail.com>

ADD . /var/www
WORKDIR /var/www

RUN apt-get update

RUN echo 'mysql-server mysql-server/root_password password root' | debconf-set-selections
RUN echo 'mysql-server mysql-server/root_password_again password root' | debconf-set-selections
RUN echo 'mysql-server mysql-server/root_password_current password ' | debconf-set-selections
RUN apt-get install -y mysql-server apache2 php5
RUN service mysql start
RUN service apache2 start
