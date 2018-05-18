#!/bin/bash

# init RabbitMQ
/usr/local/bin/docker-entrypoint.sh rabbitmq-server -detached

# Join to cluster
rabbitmqctl stop_app
rabbitmqctl join_cluster rabbit@rabbitmq1
rabbitmqctl start_app

# Show cluster status
rabbitmqctl cluster_status

# Enable mirrored queues
rabbitmqctl set_policy ha "^ha-" '{"ha-mode":"all"}'

# Restart server
rabbitmqctl stop
sleep 5
rabbitmq-server