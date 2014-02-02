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
			->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Server will be listening on this port')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$port = $input->getOption('port');

		if (!$port) {
			$port = 4567;
		}

		$server = IoServer::factory(
		    new HttpServer(
		        new WsServer(
		            $this->getContainer()->get('osiris_api.socket_server')
		        )
		    ),
		    $port
		);

	    $output->writeln("Server running on port $port");

		$server->run();		
	}
}
