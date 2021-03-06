<?php
declare(strict_types=1);
/**
 * /tests/Functional/Security/UserProviderTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Security\UserProvider;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Security\Core\User\User as CoreUser;

/**
 * Class UserProviderTest
 *
 * @package App\Tests\Functional\Security
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserProviderTest extends KernelTestCase
{
    /**
     * @var UserProvider;
     */
    private $userProvider;

    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();

        // Store container and entity manager
        $container = static::$kernel->getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $repository = UserProvider::class;

        $this->userProvider = new $repository($entityManager, new ClassMetadata(User::class));
    }

    public function testThatLoadUserByUsernameReturnsNullWithInvalidUsername(): void
    {
        static::assertNull($this->userProvider->loadUserByUsername('foobar'));
    }

    /**
     * @expectedException \Doctrine\ORM\NoResultException
     */
    public function testThatRefreshUserThrowsAnExceptionIfUserIsNotFound(): void
    {
        $user = new User();
        $user->setUsername('test');

        $this->userProvider->refreshUser($user);
    }

    public function testThatRefreshUserReturnsCorrectUser(): void
    {
        /** @var User $user */
        $user = $this->userProvider->findOneBy(['username' => 'john']);

        static::assertNotNull($user);
        static::assertInstanceOf(User::class, $user);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame($user->getId(), $this->userProvider->refreshUser($user)->getId());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @expectedExceptionMessage Instance of "Symfony\Component\Security\Core\User\User" is not supported.
     */
    public function testThatRefreshUserThrowsAnExceptionIfUserClassIsNotSupported(): void
    {
        $user = new CoreUser('test', 'password');

        $this->userProvider->refreshUser($user);
    }
}
