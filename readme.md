# Class Trees
This package can be used to visualize all dependencies in a project

It displays all classes in a project and all its connections sort of like a tree

## Commmands

```bash
php artisan class-trees:create-project --name=self
php artisan class-trees:convert-queued-classes --project-id=1
```


## Entry into understanding the code
This code is far from perfect, but it does what it should do.

To find out how the code works, I recommend starting at the `ConvertQueuedClasses` command


