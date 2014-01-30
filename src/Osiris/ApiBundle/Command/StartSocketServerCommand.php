<?php

namespace Osiris\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Osiris\ApiBundle\SocketServer;

/**
* Start socket server.
*/
class StartSocketServerCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('osiris:server:start')
			->setDescription('Start the Osiris socket server')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$server = IoServer::factory(
            new SocketServer()
        	, 8080
	    );

	    $output->writeln("Server running on port 8080");

		$server->run();

		
	}
}