<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Finder\Finder;

final class ResolveDocsTodoReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var string
     */
    private const TODO_PLACEHOLDER = '<!--- TODO';

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Resolve TODO comments in *.md files';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 840;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $finder = Finder::create()->files()
            ->name('*.md')
            ->in(getcwd())
            ->exclude('vendor');

        $this->symfonyStyle->section(sprintf('Checking %d files for "%s"', count($finder->getIterator()), self::TODO_PLACEHOLDER));

        /** @var \Symfony\Component\Finder\SplFileInfo $fileInfo */
        foreach ($finder as $fileInfo) {
            $todoFound = Strings::matchAll($fileInfo->getContents(), '#' . preg_quote(self::TODO_PLACEHOLDER) . '#');
            if ($todoFound === []) {
                continue;
            }

            // @todo add clickable file links later: https://github.com/symfony/symfony/pull/29168/files
            $this->symfonyStyle->note(sprintf(
                '[Manual] File "%s" has %d todo%s to resolve. Fix them manually.',
                $fileInfo->getPathname(),
                count($todoFound),
                $todoFound > 1 ? 's' : ''
            ));
        }
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
