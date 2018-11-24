<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\UpgradeFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symplify\MonorepoBuilder\Release\Message;

final class UpdateUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var \Shopsys\Releaser\FileManipulator\UpgradeFileManipulator
     */
    private $upgradeFileManipulator;

    /**
     * @param \Shopsys\Releaser\FileManipulator\UpgradeFileManipulator $upgradeFileManipulator
     */
    public function __construct(UpgradeFileManipulator $upgradeFileManipulator)
    {
        $this->upgradeFileManipulator = $upgradeFileManipulator;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('Update UPGRADE.md from/to headline with "%s" version', $version->getVersionString());
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 800;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        return;

        // load
        $changelogFilePath = getcwd() . '/UPGRADE.md';

        // change
        $newChangelogContent = $this->upgradeFileManipulator->processFileToString($changelogFilePath, $version);

        // save
        FileSystem::write($changelogFilePath, $newChangelogContent);

        $this->symfonyStyle->success(Message::SUCCESS);
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
