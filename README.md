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

## Usage

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

### Handle `BelongsTo` relationships

A common use case is to have a `BelongsTo` relationship between two Ozu Models. There are two possibilities:
- the relationship is not exposed to Ozu, meaning you don't want to handle it in the CMS: in this case you can define the relationship as usual in Laravel, with a dedicated DB column for the foreign key.
- If you more likely need to allow the content manager to update this relation, then there is a major contraint: you can only have one belongsTo relation per Model, with a column named `parent_id`.

Here is an example with a `Project` Model that belongs to a `Category` Model. First the migration:

```php
return new class extends Migration
{
    use MigratesOzuTable;

    public function up(): void
    {
        $this->createOzuTable('projects');

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('categories')->cascadeOnDelete();
            // ...
        });
    }
};
```

Then the `Project` Model:

```php
class Project extends Model
{
    use IsOzuModel;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    // ...

    public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig
    {
        return $config
            ->declareBelongsToField(ozuModelClass: Category::class, label: 'Project Category')
            ->addCustomField( /* ... */);
            // ...
    }
    
    // ...
}
```

With that, you can use the regular `$project->category` relationship in your codebase, and Ozu will be able to present a category selector in the Project form on the CMS.

> [!NOTE]  
> You can of course define the `HasMany` opposite of this relation in the Category Model if needed.

### Attached visuals are `Media`

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

```blade
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

### Check the demo project for an example

You can refer to the Ozu demo project [dvlpp/ozu-demo](https://github.com/dvlpp/ozu-demo) for an example of a simple project that uses Ozu.

## Restrictions

Generating static files means we can’t use request-specific features like query parameters, session, POST forms, etc. But Ozu provides solutions to keep the code as close to a classic Laravel app as possible.

### Query string

Consider this simple use case: we need to display a project list that we want to be sortable. In a classic Laravel app, we would have a route like this:

```php
Route::get('/projects')
```

And in the controller, we would check for a query parameter to sort the projects, for instance `/projects?sort=asc`.

In an Ozu project, like for any static website, we can't check for `sort` in the controller because we are generating static HTML files; you can instead:
  - put the query in a param (eg: `/projects/list/{sort}`): this will create 2 HTML files `projects/list/desc.html` and `projects/list/asc.html`.
  - Or handle the query string in front-end code (with Alpine for example).

### Pagination

For the very same reason, `?page=1` can't work with generated static HTML; instead you'll need to put the page as a segment:

```php
Route::get('/projects/index/{page}')
```

You will still be able to use `{{ $projects->links() }}` or `route('projects.index', ['page' => 2])`: Ozu overrides Laravel default Paginator to handle the page as a segment. 

### Session

By definition sessions aren’t available for static generated sites. If you really need to store session data you can use cookies or localStorage in JS.

### Forms

For forms, in the current state of Ozu, you'll need an external provider to handle submission (there are a lot of solutions, like [FieldGoal](https://fieldgoal.io/) for instance).

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
> Sharp is the underlying content management framework used by Ozu: although you really don’t have to know it to use Ozu, you can [check its website](https://sharp.code16.fr) if you are curious.

> [!NOTE]
> For now there is no way to seed production data, but it’s high in the roadmap. 

### Deploy your project

Once you have entered your content in the CMS, you can deploy the project as a static website following these steps:

1. install Ozu Github app + grant read only access to your repository
2. configure github repo + branch in Ozu dashboard (configuration menu)
3. Create a new personal access token in Netlify (https://app.netlify.com/user/applications/personal) with expiration set to "No expiration" and report it in Ozu dashboard 
4. Create Netlify site in Ozu dashboard
5. (push you latest code in your repo if needed and adapt your content in Ozu CMS)
6. click on "Deploy"!
