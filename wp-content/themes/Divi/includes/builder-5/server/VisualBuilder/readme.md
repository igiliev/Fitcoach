# VisualBuilder

## What is VisualBuilder folder?
VisualBuilder folder is consisted of classes and functionalities needed for Visual Builder to work. This is where VisualBuilder functionalities are created and initiated.

## How `VisualBuilder` is loaded?

`/server/VisualBuilder.php` is the root file and the starting point of loading Visual Builder dependencies.

`VisualBuilder` class doesn't do much by itself other than removing and adding some WordPress hooks however on construction it accepts a `DependencyTree` which tells `VisualBuilder` all the dependencies it needs to load. Each dependency loads and initiates all its core logic within itself and without knowing anything outside its scope.

Relying on composable classes offer a very testable and transparent approach as supposed to something like Singleton pattern which is hard to test reliably and masks bad code design.

This architectural approach used for `VisualBuilder` is essentially a combination of Builder pattern and Strategy pattern in OOP.

## How to extend `VisualBuilder`?

`VisualBuilder` implements Composition over Inheritance, Single Responsibility Principle and Open/Closed Principle.
 
There is little possibility of creating God objects and if trying so it would be apparent really early. We can construct building parts step-by-step and use same construction code everywhere.

We can ensure the correct long term usage of our architecture via our `interfaces`. That means if developers want to extend `VisualBuilder` they need to implement certain `interfaces` so it would be accepted and loaded by `VisualBuilder`.

For example to add a new feature (let's call it "Captcha") to `/server/VisualBuilder` you should:

1. PHP files in `/server` are loaded via **PSR-4 Autoloader**, So you need to be keen-eyed follow some rules when naming your folders, files, namespaces and classes. See: https://www.php-fig.org/psr/psr-4/
2. Create the following folder: `/server/VisualBuilder/Captcha/`
3. Create necessary classes within your folder: `CaptchaGeneration.php`
4. Implement `DependencyInterface` for your class, e.g: `class CaptchaGeneration implements DependencyInterface`
5. Your class can have all the logic and methods you need and it can even have its own dependencies, However when you implement `DependencyInterface` you need to provide a public `load()` function. This is where you need to initiate, register and call your Captcha core functions or add them to WP via actions and filters.
6. If your class has a lot of methods, It is strongly suggested to separate them using `Traits`. Try to create meaningful traits and combine related functions under the same trait and use them inside your class. This keeps the class clean and is easier to read and maintain.
7. Once done, You can edit `/server/VisualBuilder/VisualBuilder.php` and add your new shiny class to `$dependency_tree`. This `$dependency_tree` is then used by `VisualBuilder` to load all its dependencies.
