<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console\Command;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;
use Contentful\Delivery\Tool\ClassGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * The GenerateClassesCommand can be used to generate a set of classes for every content type. This yields improved performance.
 *
 * @api
 */
class GenerateClassesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate-classes')
            ->setDefinition([
                new InputArgument(
                    'space-id', InputArgument::REQUIRED,
                    'ID of the Space to generate Classes for.'
                ),
                new InputArgument(
                    'token', InputArgument::REQUIRED,
                    'Token to access the space.'
                ),
                new InputArgument(
                    'dest-path', InputArgument::REQUIRED,
                    'The path to write the generated classes to.'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $spaceId = $input->getArgument('space-id');
        $token = $input->getArgument('token');
        $destPath = $input->getArgument('dest-path');

        if (!file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        }
        if (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        $generator = $this->getClassGenerator();

        $client = new Client($token, $spaceId);
        $contentTypes = $client->getContentTypes(new Query);

        foreach ($contentTypes as $contentType) {
            $code = $generator->generateEntryClass($contentType);
            file_put_contents($destPath . '/' . $generator->getClassName($contentType) . '.php', $code);
        }
    }

    /**
     * Returns the ClassGenerator used by this command. Override this method if you need to customize the generated classes.
     *
     * @return ClassGenerator
     *
     * @api
     */
    protected function getClassGenerator()
    {
        return new ClassGenerator;
    }
}
