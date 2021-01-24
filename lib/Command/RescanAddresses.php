<?php

/**
 * Nextcloud - maps
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author anthraxn8b
 */

namespace OCA\Maps\Command;

use OCP\Encryption\IManager;
use OCP\Files\NotFoundException;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OCP\IConfig;

use OCA\Maps\Service\AddressService;

class RescanPhotos extends Command {

    protected $userManager;

    protected $output;

    protected $encryptionManager;

    private $addressService;

    public function __construct(IUserManager $userManager,
                                IManager $encryptionManager,
                                AddressService $addressService,
                                IConfig $config) {
        parent::__construct();
        $this->userManager = $userManager;
        $this->encryptionManager = $encryptionManager;
        $this->addressService = $addressService;
        $this->config = $config;
    }
    protected function configure() {
        $this->setName('maps:scan-addresses')
            ->setDescription('Rescan address data')
            ->addArgument(
                'user_id',
                InputArgument::OPTIONAL,
                'Rescan address data for the given user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->encryptionManager->isEnabled()) {
            $output->writeln('Encryption is enabled. Aborted.');
            return 1;
        }
        $this->output = $output;
        $userId = $input->getArgument('user_id');
        if ($userId === null) {
            $this->userManager->callForSeenUsers(function (IUser $user) {
                $this->rescanUserAddresses($user->getUID());
            });
        } else {
            $user = $this->userManager->get($userId);
            if ($user !== null) {
                $this->rescanUserAddresses($userId);
            }
        }
        return 0;
    }

    private function rescanUserAddresses($userId) {
        $this->addressService->lookupMissingGeo(999999);
    }
}
