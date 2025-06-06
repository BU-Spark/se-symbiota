FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
ENV MYSQL_ROOT_PASSWORD=root

# Install base tools
RUN apt-get update && apt-get install -y \
    gnupg \
    wget \
    lsb-release \
    debconf-utils \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Download MySQL APT config package
RUN wget https://dev.mysql.com/get/mysql-apt-config_0.8.24-1_all.deb

# Preseed debconf to select MySQL 5.7 without interaction
RUN echo "mysql-apt-config mysql-apt-config/select-server select mysql-5.7" | debconf-set-selections && \
    echo "mysql-apt-config mysql-apt-config/select-product select Ok" | debconf-set-selections && \
    dpkg -i mysql-apt-config_0.8.24-1_all.deb && \
    rm mysql-apt-config_0.8.24-1_all.deb

# Install MySQL 5.7 (exact version)
RUN apt-get update && \
    echo "mysql-community-server mysql-community-server/root-pass password ${MYSQL_ROOT_PASSWORD}" | debconf-set-selections && \
    echo "mysql-community-server mysql-community-server/re-root-pass password ${MYSQL_ROOT_PASSWORD}" | debconf-set-selections && \
    apt-get install -y mysql-server=5.7.44-1ubuntu22.04 && \
    rm -rf /var/lib/apt/lists/*

# Expose MySQL port
EXPOSE 3306

# Default command
CMD ["mysqld"]