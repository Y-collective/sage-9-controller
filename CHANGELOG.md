### 3.0.0:
* Modernize codebase for PHP 8.2+
* Declare strict types in all files
* Add typed properties, parameter types, and return types to all classes
* Fix `__setDatafromModuleAcf()` method typo → `__setDataFromModuleAcf()`
* Fix undefined array key risks for `__app` and `__store` with null coalescing
* Fix `array_filter` with side-effect assignment in `__setDataFromMethods()`
* Fix `Loader::setPath()` to check `class_exists()` before `ReflectionClass`
* Fix `Loader::setClassesAlias()` to avoid alias collisions and missing class errors
* Fix `Loader::addBodyDataClasses()` uninitialized `$classes` array
* Fix `Coder::render()` empty data edge case with `call_user_func_array('array_merge', ...)`
* Fix `Utils::doesFileContain()` to suppress errors on unreadable files
* Fix `Acf::recursiveSnakeCase()` to convert kebab-case keys at all array levels
* Fix `Acf::setData()` to handle null returns from `get_field()`/`get_fields()`
* Remove redundant `final` keyword from private methods
* Rebase php constraint to `^8.2` (covers 8.2–8.x)
* Change package `type` from `"package"` to `"library"`
* Fix `homepage` to point to fork repository

### 2.1.2:
* Fixes

### 2.1.1:
* Update Brain/Hierarchy to 2.4.0 for CPT templates [#86](https://github.com/soberwp/controller/issues/86)
* Add filter sober/controller/sage/namespace to allow for a custom Sage namespace [#104](https://github.com/soberwp/controller/issues/104)
* Update ACF class to support taxonomy fields by default by using get_queried_object() [#101](https://github.com/soberwp/controller/issues/101)
* Bug fix for ACF class if no fields on page/post [#102](https://github.com/soberwp/controller/issues/102)
* Allow interaction in Controller with the $post object by using $this->post vs $this->data['post']

### 2.1.0:
* Update deps
* Pass in field data from Acf Options under App class
* Change $this->data from private to protected param
* Fix $post bug not appearing in the $this->data
* Fix Controller overriding filter $data
* Add filter to return Acf data as array
* Add __before and __after lifecycles
* @code and @codeif

### 2.0.1:
* Fix bug assuming Controllers/ folder name

### 2.0.0:
* PSR4 loading
* Template overrides for those underscores
* Pass in field data from Acf automatically
* Debugger to include static methods
* Improve Debugger results
* Dependency injection
* Bug fixes
* Change default path from resources/controllers to app/controllers

### 9.0.0-beta.3:
* Changed to Composer package
* Fix for app Controller bug
* Rename base to app
* Change default path from src/controllers to resources/controllers

### 9.0.0-beta.2.1:
* Fix for base Controller bug

### 9.0.0-beta.2:
* Align with Sage9 versioning
* Enable the use of __construct within the child Class

### 1.0.2:
* Prevent public static methods from being passed onto data
* Class alias for use in template

### 1.0.1:
* Pass on default post data for posts
* Show $post in the controller debugger

### 1.0.0:
* Release
