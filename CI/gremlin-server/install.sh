#!/bin/bash

# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Install gremlin-server
wget --no-check-certificate -O $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/$GREMLINSERVER_VERSION-incubating/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip
unzip $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip -d $HOME/

# get gremlin-server configuration files
cp ./CI/gremlin-server/gremlin-spider-script.groovy $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/scripts/
cp ./CI/gremlin-server/gremlin-server-spider.yaml $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/conf/

# get neo4j dependencies
cd $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating

# Start gremlin-server in the background and wait for it to be available
bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd $TRAVIS_BUILD_DIR
sleep 30
