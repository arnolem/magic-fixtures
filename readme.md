Magic Fixtures
==================

Magic Fixtures allows you to quickly and easily create fixtures to simplify development and testing.

## Installing

[PHP](https://php.net) 8.2+ and [Composer](https://getcomposer.org) are required.

```bash
composer req --dev arnolem/magic-fixtures
```

## Main features

 - Fixtures are native PHP classes.
 - Fixtures are automatically loaded from a directory.
 - Fixtures are compatible with ``Psr\Container\ContainerInterface`` to integrate with all frameworks.
 - It manages the dependencies between fixtures using the needs() method.

## Usage

### Installation

Installation example for Symfony:

```php
namespace Tests\Account;

use Arnolem\MagicFixtures\MagicFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccountCase extends KernelTestCase
{

    /**
     * @throws DirectoryNotFoundException
     */
    public static function setUpBeforeClass(): void
    {
        // Boot Symfony Kernel
        self::bootKernel();

        // Provide Symfony Container to Magic Fixtures
        $magicFixtures = new MagicFixtures(self::getContainer());
        
        // Load all Fixtures form "/Tests/Fixtures/*"
        $magicFixtures->loadFromDirectory(__DIR__ . '/Fixture');
        
        // Executes Fixtures
        $magicFixtures->execute();
    }
    
    public function testTrue(): void
    {
        $this->assertTrue(true);
    }

}
```

### Your first fixture

```php
namespace Tests\Fixture;

use App\Domain\Account;
use App\Domain\Company;
use App\Infrastructure\AccountPersister;
use Arnolem\MagicFixtures\Interface\Fixture;
use Psr\Container\ContainerInterface;

readonly class AccountFixture implements Fixture
{

    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function execute(): void
    {
        $account = new Account(
            firstname : 'Arnaud',
            name : 'Lemercier',
        );
        
        $this->accountPersister->save($account);
    }

    public function needs(): array
    {
        return [
            Company::class
        ];
    }
}
```

Another depends Fixture

```php
namespace Tests\Fixture;

use App\Domain\Company;
use App\Infrastructure\AccountPersister;
use Arnolem\MagicFixtures\Interface\Fixture;
use Psr\Container\ContainerInterface;

readonly class CompanyFixture implements Fixture
{

    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function execute(): void
    {
        $company = new Company(
            name : 'Wixiweb',
        );
        
        $this->companyPersister->save($company);
    }
}
```

## Credits

- Arnaud Lemercier is based on [Wixiweb](https://wixiweb.fr).

## License

Magic Fixtures is licensed under [The MIT License (MIT)](LICENSE).
