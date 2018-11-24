<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\Stage;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\InterdependencyUpdater;
use Symplify\MonorepoBuilder\Package\PackageNamesProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Message;

final class SetMutualDependenciesToDevMasterReleaseWorker implements ReleaseWorkerInterface, StageAwareReleaseWorkerInterface
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var \Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider
     */
    private $composerJsonProvider;

    /**
     * @var \Symplify\MonorepoBuilder\InterdependencyUpdater
     */
    private $interdependencyUpdater;

    /**
     * @var \Symplify\MonorepoBuilder\Package\PackageNamesProvider
     */
    private $packageNamesProvider;

    /**
     * @var string
     */
    private const DEV_MASTER = 'dev-master';

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider $composerJsonProvider
     * @param \Symplify\MonorepoBuilder\InterdependencyUpdater $interdependencyUpdater
     * @param \Symplify\MonorepoBuilder\Package\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(SymfonyStyle $symfonyStyle, ComposerJsonProvider $composerJsonProvider, InterdependencyUpdater $interdependencyUpdater, PackageNamesProvider $packageNamesProvider)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->composerJsonProvider = $composerJsonProvider;
        $this->interdependencyUpdater = $interdependencyUpdater;
        $this->packageNamesProvider = $packageNamesProvider;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('Set mutual package dependencies to "%s" version', self::DEV_MASTER);
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 260;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->interdependencyUpdater->updateFileInfosWithPackagesAndVersion(
            $this->composerJsonProvider->getPackagesFileInfos(),
            $this->packageNamesProvider->provide(),
            self::DEV_MASTER
        );

        $this->symfonyStyle->success(Message::SUCCESS);
        $this->symfonyStyle->note('[Manual] Commit changes of composer.json files');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}