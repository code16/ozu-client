# Client companion for Ozu 

Ozu brings a nice solution to develop and maintain a static website with a dynamic content, keeping the productivity and great DX of Laravel.

The project is in private beta for now, but you can request an access here: [ozu.code16.fr](https://ozu.code16.fr). You can also refer to [this blog post](https://code16.fr/posts/introducing-ozu-a-static-website-deployment-solution-for-laravel-projects/) to learn more about the project.

## Installation

Require the package:

```bash
composer require code16/ozu-client
```

Publish the config file:

```bash
php artisan vendor:publish --tag="ozu-client-config"
```

## Getting started

### Routes, controllers, views

Create your routes, controllers, views, etc. as you would do for a regular Laravel project — with a few restrictions in mind:
- TODO
- TODO
- ...

### Models are Ozu collections

The Models you want to expose to Ozu (meaning: for which you want to configure the content management tool) must follow these rules:

First your migration must use the `Code16\OzuClient\Support\Database\MigratesOzuTable` trait, and call `$this->createOzuTable('my-table');` in the `up()` method: this will create the table with the required columns for Ozu, and you can then add your own columns.

```php
use Code16\OzuClient\Support\Database\MigratesOzuTable;
// ...

return new class extends Migration
{
    use MigratesOzuTable;

    public function up(): void
    {
        $this->createOzuTable('projects');

        Schema::table('projects', function (Blueprint $table) {
            $table->string('country')->nullable();
            // ...
        });
    }
};
```

The base Ozu model comes with these attributes:
- `title`
- `content`
- `slug` (automatically filled from the title)
- `order`
- `cover` (as a `Media`, see below)

Second your Model must implement the `Code16\OzuClient\Eloquent\IsOzuModel` trait, and implement 3 static methods:

- `configureOzuCollection` is where you can define the collection's label, icon, and some options like the publication state, the creatable state, and the deletable state.
- `configureOzuCollectionList` is where you can define the columns to display in the list view.
- `configureOzuCollectionForm` is where you can define the custom fields to display in the form view.

Here's an example for a `Project` Model with one additional field (`country`) and some basic configuration:

```php
use Code16\OzuClient\Eloquent\IsOzuModel;
// ...

class Project extends Model
{
    use IsOzuModel;

    // ...

    public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig
    {
        return $config
            ->setLabel('Projects')
            ->setIcon('fa-ruler-combined')
            ->setHasPublicationState()
            ->setIsCreatable()
            ->setIsDeletable(false);
    }

    public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig
    {
        return $config
            ->addColumn(OzuColumn::makeImage('cover', 1))
            ->addColumn(OzuColumn::makeText('title', 5)->setLabel('Title'))
            ->addColumn(OzuColumn::makeText('country', 6)->setLabel('Country'))
            ->setIsSearchable()
            ->setIsReorderable();
    }

    public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig
    {
        return $config
            ->addCustomField(
                OzuField::makeText('country')
                    ->setLabel('Country')
                    ->setValidationRules(['required'])
            );
    }
}
```

> [!NOTE]  
> This configuration will be used by Ozu to properly display the collection in the content management tool. It has no effect in your local codebase.

### Attachments and visuals are `Media`s

If you want to attach images to your Models, leverage the `Code16\OzuClient\Eloquent\Media` model via a `MorphOne` or a `MorphMany` relation:

```php
use Code16\OzuClient\Eloquent\Media;
// ...

class Project extends Model
{
    use IsOzuModel;

    public function visuals(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')
            ->where('model_key', 'visuals')
            ->orderBy('order');
    }

    public function ogImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('model_key', 'ogImage');
    }
    
    // ...
}
```

You must define the `model_key` in the relation to differentiate the different types of media you can attach to your models.

You can then use this relation in your views to display the images, and leverage the `thumbnail()` method to get the URL of the image in the desired size:

```php
@if(count($project->visuals))
    <div class="mt-12">
        <div class="grid sm:grid-cols-3 grid-cols-2 gap-4">
            @foreach($project->visuals as $visual)
                <img class="aspect-square" src="{{ $visual->thumbnail(400, fit: true) }}" alt="">
            @endforeach
        </div>
    </div>
@endif
```

### Local (dev) seeder

To ease the development of your project, you can use the `OzuSeeder` class to seed your local database with some dummy data:

```php
use Code16\OzuClient\Support\Database\OzuSeeder;
// ...

class DatabaseSeeder extends OzuSeeder
{
    public function run(): void
    {
        // this will delete any remaining seeded Media file
        $this->clearMediaDirectory();

        Project::factory()
            ->count(12)
            ->has(Media::factory()->image('cover')->withFile(), 'cover')
            ->has(Media::factory()->image('visuals')->withFile()->count(3), 'visuals')
            ->sequence(fn ($sequence) => [
                'order' => $sequence->index + 1,
                'country' => fake()->country(),
            ])
            ->create();
            
        // ...
    }
}
```

## Go for production

Once your project is ready, you can deploy it to Ozu.

### Configure the project in Ozu

First declare your Ozu collection in the `config/ozu-client` configuration file:

```bash
php artisan vendor:publish --tag="ozu-client-config"
```

```php
// config/ozu-client.php

return [
    // ...
    'collections' => [
        App\Models\Project::class,
        // ...
    ],
];
```

Next you need to configure your credentials in the `.env` file:

```dotenv
OZU_API_KEY=[your-api-key]
OZU_WEBSITE_KEY=[your-website-key]
```

Then launch the `ozu:configure-cms` command:

```bash
php artisan ozu:configure-cms
```

This command will create a new collection in Ozu for each of your declared Models, and will configure the lists and forms according to the methods you defined in your Models. You must repeat this command each time you add or update an Ozu Model.

At this stage, you should be able to see your custom CMS at the address https://ozu.code16.fr/sharp. Here you can manage the content of your collections.

> [!NOTE]
> For now there is no way to seed production data, but it's in the roadmap. 

### Deploy your project

To deploy your project in the actual production space, you'll need a few more steps:

- attach you Github repository to your Ozu project
- Netlify
- ...
