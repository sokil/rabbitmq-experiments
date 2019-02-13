<?php

namespace Command;

use PhpAmqpLib\Connection\AbstractConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class TopicCommand extends Command
{
    /**
     * @var AbstractConnection
     */
    private $amqpConnection;

    public function __construct($name = null, AbstractConnection $amqpConnection)
    {
        parent::__construct($name);

        $this->amqpConnection = $amqpConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $this->amqpConnection->channel();

        $exchangeName = 'FanoutExchange';

        // create fanout exchange
        $channel->exchange_declare(
            $exchangeName,
            'fanout',
            false,
            false,
            false
        );

        $queueCount = 8;

        // create queues
        for ($i = 0; $i < $queueCount; $i++) {
            $queueName = sprintf('ha-fanout-bound-queue-%d', $i);

            // declare queue
            $channel->queue_declare(
                $queueName,
                false,
                false,
                false,
                false
            );

            // bind to fanout exchange
            $channel->queue_bind(
                $queueName,
                $exchangeName,
                ''
            );
        }


        // publish messages
        for ($i = 1000; $i < 2000; $i++) {
            $channel->basic_publish(
                new AMQPMessage($i),
                $exchangeName,
                ''
            );
        }

        /** @var QuestionHelper $question */
        $question = $this->getHelper('question');
        $isConsumeRequired = $question->ask(
            $input,
            $output,
            new ConfirmationQuestion('Consume messages? ', 'y')
        );

        if ($isConsumeRequired) {
            for ($i = 0; $i < $queueCount; $i++) {
                // consume messages
                $channel->basic_consume(
                    sprintf('ha-fanout-bound-queue-%d', $i),
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
    }
}