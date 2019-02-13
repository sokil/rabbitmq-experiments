<?php

/**
 * Check who faster: fanout or topic with routing_key ="#"
 */

namespace Command;

use PhpAmqpLib\Connection\AbstractConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FanoutVsTopicBenchCommand extends Command
{
    /**
     * @var AbstractConnection
     */
    private $amqpConnection;

    /**
     * @param null $name
     * @param AbstractConnection $amqpConnection
     */
    public function __construct($name = null, AbstractConnection $amqpConnection)
    {
        parent::__construct($name);

        $this->amqpConnection = $amqpConnection;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $this->amqpConnection->channel();

        // queue names
        $fanoutExchangeName = 'FanoutExchange';
        $fanoutQueueName = 'ha-fanoutVsTopic-bench-fanout';

        // create fanout exchange
        $channel->exchange_declare(
            $fanoutExchangeName,
            'fanout',
            false,
            false,
            false
        );

        // declare queue
        $channel->queue_declare(
            $fanoutQueueName,
            false,
            false,
            false,
            false
        );

        // bind to fanout exchange
        $channel->queue_bind(
            $fanoutQueueName,
            $fanoutExchangeName,
            ''
        );

        // publish messages
        for ($i = 1000; $i < 2000; $i++) {
            $channel->basic_publish(
                new AMQPMessage($i),
                $fanoutExchangeName,
                ''
            );
        }

        // consume messages
        $channel->basic_consume(
            $fanoutQueueName,
            '',
            false,
            true,
            false,
            false,
            function (AMQPMessage $message) {
                echo $message->getBody() . PHP_EOL;
            }
        );

        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
