Fixtures Documentation Bundle
=========

[![Package version](https://img.shields.io/packagist/v/adlarge/fixtures-documentation-bundle.svg?style=flat-square)](https://packagist.org/packages/adlarge/fixtures-documentation-bundle)
[![Build Status](https://travis-ci.org/adlarge/fixtures-documentation-bundle.svg?branch=master&style=flat-square)](https://travis-ci.org/adlarge/fixtures-documentation-bundle?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/adlarge/fixtures-documentation-bundle/badge.svg?branch=master)](https://coveralls.io/github/adlarge/fixtures-documentation-bundle?branch=master)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)

This Symfony bundle generates and exposes a documentation of your fixtures.
An action to reload your fixtures can also be configured.

The goal of this bundle is to allow testers to be independent, they can see data and reload fixtures when they want to test again.
    
## What does it do

It will generate a json file with all the data to be used in a twig page to display fixtures to the end user.

To add data to this json file you can process full manually, manually by passing the entity or full automatically.
The main data to know of are : the type of the fixture (section title), the id of the fixture and the data of the fixture.

It will display a page with a menu corresponding to the different sections (with links), and data tables regrouped by section.
If you used links, it will display some columns with visible links to go directly to the linked object.

When it encounter a property it can have 3 behaviors :

* If it's a simple (scalar) property, it will display it (string, bool, int, etc)
* If it's an array, it will display the total of elements in this array
* If it's an object and it got a __toString public method, it will display the result of this method. 
  If this class is in your configuration of entities, it will add a link toward it.

## Working projects examples

Some external projects are available to see and test the behavior of this bundle. You'll have to clone it next to this bundle 
and follow the documentation to make it work and see the prepared result by yourself.

* The project for [Manual case](https://github.com/bluepioupiou/fixture-doc-manualcase) : if you want to manually manage your documentation
* The project for [Doctrine automatic case](https://github.com/bluepioupiou/fixture-doc-autocase) : if you use doctrine and just want to configure entities and properties to document and let the bundle do
* The project for [Alice case](https://github.com/bluepioupiou/fixture-doc-alicecase) : if you load fixtures through Alice bundle and want to let the bundle do with your configuration

## Installation

This is installable via [Composer](https://getcomposer.org/) as
[adlarge/fixtures-documentation-bundle](https://packagist.org/packages/adlarge/fixtures-documentation-bundle):

    composer require --dev adlarge/fixtures-documentation-bundle

The default url to access the documentation is **/fixtures/doc**

## Configuration

Add the bundle to your `config/bundles.php` :

    return [
        // ...
        Adlarge\FixturesDocumentationBundle\AdlargeFixturesDocumentationBundle::class => ['dev' => true],
        // ...
    ];

Add the routing file `config/routes/dev/adlarge_fixtures_documentation.yaml` and paste the following content :

    AdlargeFixturesDocumentation:
        resource: '@AdlargeFixturesDocumentationBundle/Resources/config/routing.yml'

You can define vars by creating the file `config/packages/dev/adlarge_fixtures_documentation.yaml` :

    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
            - ....
        listenedCommand: 'doctrine:fixtures:load'
        enableAutoDocumentation: true
        configEntities:
            Product:
                - id
                - name
                - category
            Customer:
                - firstname
                - lastname

* title has a default value 'Fixtures documentation'
* listenedCommand has a default value 'doctrine:fixtures:load'. For Alice bundle, you can set it to 'hautelook:fixtures:load'
* reloadCommand is an optional array of commands you want to run from the view. If present a button to run these command will be visible in this view
* enableAutoDocumentation is a boolean default to false. Set it to true if you want that all entities in fixtures are auto documented in postPersist
* configEntities is an optional array of configurations for your entities you want to auto-document

Then you can install assets :

    php bin/console assets:install --symlink

## Examples

### Adding fixtures manually

To add fixtures to your documentation you have to get the manager in your fixtures file :

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();

        $doc->addFixture('Customer', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@test.fr'
        ]);

        $doc->addFixture('Products', [
            'name' => 'Product 1',
            'owner' => 'John Doe'
        ]);

        $doc->addFixture('Products', [
            'name' => 'Product 2',
            'owner' => 'John Doe'
        ]);
    }
}
```

Result :

![GitHub Logo](/doc/img/fixtures-documentation-manual.png)

### Link fixtures manually

It's possible to link fixtures between them, for example, if we have a list of comments with an author field that represent a user, we can link fixtures like this :

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();        

        $userFixture = $doc->addFixture('Users', [
            'first name' => 'John',
            'last name' => 'Doe',
            'email' => 'john.doe@test.fr'
        ]);
        $doc->addFixture('Product', [
            'name' => 'Product 1',
            'author' => 'John Doe',
        ])
            ->addLink('author', $userFixture);

        $manager->flush();
    }
}
``` 

The `addLink` method needs the field on which we want to create the link and the Fixture we want to link to.

Result :

![GitHub Logo](/doc/img/fixtures-documentation-link.png)

### Sharing fixtures

It's possible to share fixtures between files. For this two methods are available on the Documentation object :

* addLinkReference('ref', $fixture)
* getLinkReference('ref')

### Adding fixtures with configuration and entity

If configured well with the `configEntities` option, you can use the method `addFixtureEntity`.

Several scenarios :

#### Fully manual

If you provide in details the properties you want for an entities, only these properties of theses entities will be documented

It will parse scalar properties and can check public properties as well as private ones with a getter (property, getProperty(), hasProperty(), isProperty()).
It will parse non scalar properties as well, if it's an array it will display the count, if it's an entity it will display the result of __toString if it exists.
It will ignore non existing properties.

With the following configuration :

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
            Product:
                - name
                - category
                - owner
            Customer:
                - firstname
                - lastname
```

You can use 

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();
        
        $customer = (new Customer())
            ->setId(1)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('john.doe@test.fr');

        $doc->addFixtureEntity($customer);

        $product = (new Product())
            ->setId(1)
            ->setName("Product 1")
            ->setCategory("Category 1")
            ->setTags(['tag1', 'tag2'])
            ->setOwner($customer);

        $doc->addFixtureEntity($product);

        $product = (new Product())
            ->setId(2)
            ->setName("Product 2")
            ->setCategory("Category 2")
            ->setTags(['tag1', 'tag3', 'tag4'])
            ->setOwner($customer);

        $doc->addFixtureEntity($product);
    }
}
```

Result :

![GitHub Logo](/doc/img/fixtures-documentation-link.png)

N.B. : In this configuration, only the links toward configured entities will be present.

#### Configure only entities

If you provide only the entities names, only these entities will be documented but with all their accessible properties

It will take all public methods starting with 'get' and use them to document each entity.

Example :

with configuration

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
            Product:
```

With the following class

```php
class Product
{
    private $id;

    private $name;

    private $category;
    
    private $tags; 

    // Here you have setters of the class
    // ...

    // Here the getters
    private function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function getTags(): array
    {
        return $this->tags;
    }
}
```

with the first example, you'll see the same
* plus tags from product because it has a public getter
* minus category from product because it doesn't have a getter
* minus Customer entities, the option being absent in configuration, it won't be documented

Result :

![GitHub Logo](/doc/img/fixtures-documentation-entities.png)

N.B. : In this configuration, only the links toward declared entities will be present.

#### Fully automatic configuration

If you don't provide configEntities or let it empty, the bundle will document every entities with all their properties.

It will take as well all public methods starting with 'get' and use them to document each entity.


Example :

with configuration

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
    #or
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
```

with the first example, you'll see the same
* plus tags from product because it has a getter
* minus category from product because it doesn't have a getter
* minus Customer entities, the option being absent in configuration, it won't be documented


Result :

![GitHub Logo](/doc/img/fixtures-documentation-empty.png)

N.B. : In this configuration, because all the entities will be present, all the links are present too.

### Adding fixtures fully automatically

You can use 'enableAutoDocumentation' configuration. If set to 'True' this configuration will automatically
document all objects according to 'configEntities' configuration when they are postPersist in database.

The postPersist is checked only where you configure the bundle (hopefully dev and test/acceptance) and when you launch the listenedCommand. 

#### With doctrine

With this example configuration 

```yaml
    adlarge_fixtures_documentation:
      title: Documentation auto
      enableAutoDocumentation: true
```

You just have to persist entities in the code run when your listenedCommand is resolved

```php
    $john = (new Customer())
        ->setFirstname('John')
        ->setLastname('Doe')
        ->setEmail('john.doe@test.fr');

    $manager->persist($john);

    $product = (new Product())
        ->setName("Product 1")
        ->setCategory("Category 1")
        ->setOwner($john)
        ->setTags(['tag1', 'tag2']);

    $manager->persist($product);

    $product = (new Product())
        ->setName("Product 2")
        ->setCategory("Category 2")
        ->setOwner($john)
        ->setTags(['tag2', 'tag2', 'tag3']);

    $manager->flush();
```

#### With Alice bundle

With this example configuration 

```yaml
    adlarge_fixtures_documentation:
      title: Documentation alice
      enableAutoDocumentation: true
      listenedCommand: hautelook:fixtures:load
      reloadCommands:
        - php bin/console hautelook:fixtures:load
```

You can then use the yaml configuration to load entities

```yaml
    App\Entity\Customer:
      john:
        firstname: John
        lastname: Doe
        email: john.doe@test.com
    
    App\Entity\Product:
      product1:
        name: product 1
        owner: '@john'
      product2:
        name: product 2
        owner: '@john'
```


## Generate documentation

To generate the doc you only have to run `php bin/console doctrine:fixtures:load` or the command you've configured on your project.

## Development

To make it run on your environment you have to install :

    composer
    php (7.1 or higher)
    PHP extensions
    * php-xml
    * php-mbstring

To run tests on your env, run these commands. Each dev must cover 100% of code before PR

    make test
    make coverage