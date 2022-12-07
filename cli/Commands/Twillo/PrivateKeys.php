<?php
namespace Studip\Cli\Commands\Twillo;

use Config;
use EduSharingHelper;
use Studip\Cli\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PrivateKeys extends AbstractCommand
{
    protected static $defaultName = 'twillo:generate-private-keys';


    protected function configure(): void
    {
        $this->setDescription('Generate private keys for twillo');
        $this->setHelp('This command will generate the private keys for the twillo-integration');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $appId = str_replace(' ', '-', Config::get()->UNI_NAME_CLEAN);
        $privateKey = $GLOBALS['STUDIP_BASE_PATH'] . '/config/twillo-private.key';
        $properties = $GLOBALS['STUDIP_BASE_PATH'] . '/config/twillo.properties.xml';

        if (!file_exists($privateKey)) {
            $key = EduSharingHelper::generateKeyPair();
            $success = file_put_contents(
                $properties,
                EduSharingHelper::generateEduAppXMLData($appId, $key['publickey'])
            );

            if ($success !== false) {
                file_put_contents(
                    $privateKey,
                    $key['privatekey']
                );
                Config::get()->store('OERCAMPUS_TWILLO_APPID', $appId);

                $io->success('"Wrote config/twillo.properties.xml file. Next steps');
                $io->note([
                    'Send the file config/twillo.properties.xml to the twillo.de administrators support.twillo@tib.eu . If they ask you to change the app-ID, you can do this in the system at Admin -> System -> Configuration by editing the config value OERCAMPUS_TWILLO_APPID.',
                    'Leave the config/twillo-private.key file as it is in the config folder. This file is important for the API to work',
                    'You need to either have or create a datafield for users which contains the DFN-AAI-ID of a person. Without this ID in the datafield a user cannot upload to twillo.de due to technical restrictions',
                    'Copy the datafield_id of this datafield (you find this in the DB-table datafields). In Stud.IP go to Admin -> System -> Configuration and edit the config value OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD. What you inserted, should look like 6a9ee3ca3685d2551698a3dc6f0f0eff',
                    'Once the twillo.de adminstrators received your file and registered your Stud.IP, you can enable the connection to twillo: Go to Admin -> System -> Configuration and edit the config value OERCAMPUS_ENABLE_TWILLO and switch it on.',
                    'There you go. From now on all your users with a given DFN-AAI-ID should be able to upload their OERs to twillo.de.'
                ]);
            } else {
                $io->error('No permission to write into folder config. Cannot save private and public keys');
            }
        } else {
            $io->error('Private key already exists. Remove it or be happy that it is there.');
        }
        return Command::SUCCESS;
    }
}
