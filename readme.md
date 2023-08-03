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
 - The created objects can be stored for use in other fixtures.
 - The objects can be retrieved by identifier, but also by class, by tag, or randomly.
 - Randomly retrieve objects that match a tag.
 - Integration of Faker to generate fake data.

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
use Arnolem\MagicFixtures\Fixture;

readonly class AccountFixture extends Fixture
{

    public function __construct()
    {
        $this->accountPersister = $this->getService(AccountPersister::class);
    }

    public function execute(): void
    {
        $account = new Account(
            firstname: 'Arnaud',
            name: 'Lemercier',
            company: $this->getRandomReference(Company::class);
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
use App\Infrastructure\CompanyPersister;
use Arnolem\MagicFixtures\Fixture;

readonly class CompanyFixture extends Fixture
{

    public function __construct()
    {
        $this->companyPersister = $this->getService(CompanyPersister::class);
    }

    public function execute(): void
    {
    
        // Create a default and activated company
        $company = new Company(
            id: 0
            name: 'Wixiweb',
            isActivate: true,
        );
        
        $this->companyPersister->save($company);
        $this->addReference($company, $company->getId(), ['activate', 'default']);
            
        // Create others 10 inactivates companies
        for ($idCompany = 1; $idCompany <= 10; $idCompany++) {
        
            $company = new Company(
                id: $idCompany
                name: $this->faker->company(),
                isActivate: false,
            );
            
            $this->companyPersister->save($company);
            $this->addReference($company, $company->getId();
        }
        
    }
}
```

## Credits

- Arnaud Lemercier is based on [Wixiweb](https://wixiweb.fr).

## License

Magic Fixtures is licensed under [The MIT License (MIT)](LICENSE).
