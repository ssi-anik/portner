<?php namespace Portner\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelpCommand extends Command
{
	protected function configure () {
		$this->setName("Hash:Hash")
			 ->setDescription("Hashes a given string using Bcrypt.")
			 ->addArgument('Password', InputArgument::REQUIRED, 'What do you wish to hash)');
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$input = $input->getArgument('Password');

		$output->writeln('Your password hashed: ' . $input);
	}
}