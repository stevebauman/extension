<?php namespace Orchestra\Extension\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExtensionCommand extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'orchestra:extension';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Orchestra\Extension commandline tool';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$name = $this->argument('name');

		switch ($action = $this->argument('action'))
		{
			case 'install' :
			case 'upgrade' :
				$this->fireMigration();
				$this->info('orchestra/extension has been migrated');
				break;
			case 'detect' :
				$this->fireDetect();
				break;
			case 'activate' :
				$this->fireActivate($name);
				break;
			case 'deactivate' :
				$this->fireDeactivate($name);
				break;
			default :
				return $this->error("Invalid action [{$action}].");
		}

		$this->laravel['orchestra.memory']->finish();
	}

	/**
	 * Fire migration process.
	 *
	 * @access protected
	 * @return void
	 */
	protected function fireMigration()
	{
		$this->call('migrate', array('--package' => 'orchestra/memory'));
	}

	/**
	 * Fire extension detection.
	 *
	 * @access protected
	 * @return void
	 */
	protected function fireDetect()
	{
		$service    = $this->laravel['orchestra.extension'];
		$extensions = $service->detect();

		if (empty($extensions))
		{
			return $this->line("<comment>No extension detected!</comment>");
		}

		$this->line("<info>Detected:</info>");

		foreach ($extensions as $name => $options)
		{
			$output = ($service->isActive($name) ? "* <info>%s</info>" : "- <comment>%s</comment>");
			
			$this->line(sprintf($output, $name));
		}
	}

	/**
	 * Fire extension activation.
	 *
	 * @access protected
	 * @param  string   $name
	 * @return void
	 */
	protected function fireActivate($name)
	{
		$this->laravel['orchestra.extension']->activate($name);
		$this->info("Extension [{$name}] activated.");
	}

	/**
	 * Fire extension activation.
	 *
	 * @access protected
	 * @param  string   $name
	 * @return void
	 */
	protected function fireDeactivate($name)
	{
		$this->laravel['orchestra.extension']->deactivate($name);
		$this->info("Extension [{$name}] deactivated.");
	}
				

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('action', InputArgument::REQUIRED, "Type of action. E.g: 'install', 'upgrade', 'detect', 'activate', 'deactivate'."),
			array('name', null, InputArgument::OPTIONAL, 'Extension Name.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}
}