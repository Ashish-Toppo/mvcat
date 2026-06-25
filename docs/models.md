# Models (Active Record)

MVCAT features a powerful Eloquent-style Active Record ORM. Models handle the interaction with your database and provide an expressive query builder. They are stored in the `app/Models/` directory and should extend `Core\Classes\Model`.

## Creating a Model

Define the `$table` property, and optionally `$fillable` or `$hidden` arrays.

Example `app/Models/User.php`:
```php
<?php

namespace App\Models;

use Core\Classes\Model;

class User extends Model {
    public static string $table = 'users';

    // Mass assignable fields
    protected array $fillable = ['name', 'email', 'password'];

    // Fields to hide when converting model to Array or JSON
    protected array $hidden = ['password'];
}
```

## Basic Usage

### Fetching Data
```php
use App\Models\User;

// Get all users
$allUsers = User::all();

// Find user by ID
$user = User::find(1);

// Query Builder
$activeUsers = User::query()
                ->where('status', '=', 'active')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
```

### Inserting and Updating
```php
// Create a new user (requires fields in $fillable)
$user = new User([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
$user->save();

// Update an existing user
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();
```

## Relationships

MVCAT's ORM supports relationships like `HasMany` and `BelongsTo`.

```php
// In app/Models/User.php
public function posts() {
    // A User has many Posts
    return $this->hasMany(Post::class, 'user_id', 'id');
}

// In app/Models/Post.php
public function user() {
    // A Post belongs to a User
    return $this->belongsTo(User::class, 'user_id', 'id');
}
```

You can now access relationships dynamically, or use Eager Loading to prevent N+1 query problems!

```php
// Eager load posts for all users!
$users = User::with('posts')->get();

foreach($users as $user) {
    echo $user->name;
    foreach($user->posts as $post) {
        echo $post->title;
    }
}
```
