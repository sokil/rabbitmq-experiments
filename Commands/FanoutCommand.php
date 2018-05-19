<?php

namespace Command;

use PhpAmqpLib\Connection\AbstractConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FanoutCommand extends Command
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
    }
}