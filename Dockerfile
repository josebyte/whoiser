FROM php:7.4-fpm-alpine

RUN apk add --update --no-cache curl ssmtp bash supervisor

# SENDMAIL config:
############################################

# root is the person who gets all mail for userids < 1000
RUN echo "root=MYEMAIL" >> /etc/ssmtp/ssmtp.conf

# Here is the gmail configuration (or change it to your private smtp server)
RUN echo "mailhub=smtp.gmail.com:587" >> /etc/ssmtp/ssmtp.conf
RUN echo "AuthUser=MYEMAIL@gmail.com" >> /etc/ssmtp/ssmtp.conf
RUN echo "AuthPass=MYPASSWORD" >> /etc/ssmtp/ssmtp.conf

RUN echo "UseTLS=YES" >> /etc/ssmtp/ssmtp.conf
RUN echo "UseSTARTTLS=YES" >> /etc/ssmtp/ssmtp.conf

# Set up php sendmail config
RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

# CRON config:
############################################
# Copy script which should be run
COPY ./whois /usr/local/bin/whois
# Run the cron every minute
#              ,------ MINUTE (0-59)
#             /  ,----- HOUR (0 - 23)
#            /  /  ,---- DAY OF MONTH (1 - 31)
#           /  /  /  ,--- MONTH (1 - 12) OR jan,feb,mar,apr ...
#          /  /  /  /  ,-- DAY OF WEEK (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
#         /  /  /  /  /
RUN echo '0 6 *  *  *  /usr/local/bin/whois' > /etc/crontabs/root

CMD ['crond', '-l 2', '-f']
