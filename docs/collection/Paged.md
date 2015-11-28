# Paged collection ```samsonos\cms\collection\Paged```
This class is a child of [Filtered collection class](Filtered.md) and integrates [Pager module](https://github.com/samsonos/php_pager) logic which is almost in every project.

As a child of:
* [Filtered collection class](Filtered.md)
* [Generic collection class](Generic.md)

It can use and override all their features. The main difference that is has build in [Filtered identifier handler callback](Filtered.md#identifier-handler-stack) for pager injection and second ```__construct($renderer, $page = 1)``` parameter for page switching.
