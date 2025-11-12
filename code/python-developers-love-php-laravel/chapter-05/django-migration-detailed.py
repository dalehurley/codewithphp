# filename: migrations/0001_initial.py
"""
Django Migration Example

This file demonstrates Django migrations with detailed examples.
Compare to Laravel migrations (laravel-migration-detailed.php).

To create a migration:
python manage.py makemigrations

To run migrations:
python manage.py migrate

To rollback:
python manage.py migrate myapp zero
python manage.py migrate myapp 0001_previous_migration
"""

from django.db import migrations, models
import django.db.models.deletion


class Migration(migrations.Migration):
    """
    Example: Create users and posts tables migration
    
    Laravel equivalent: database/migrations/2024_01_01_000001_create_users_table.php
    """
    initial = True
    
    dependencies = []
    
    operations = [
        # Create users table
        migrations.CreateModel(
            name='User',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=255)),
                ('email', models.EmailField(max_length=255, unique=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
        ),
        
        # Create posts table with foreign key
        migrations.CreateModel(
            name='Post',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('title', models.CharField(max_length=200)),
                ('content', models.TextField()),
                ('published_at', models.DateTimeField(null=True, blank=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('author', models.ForeignKey(
                    on_delete=django.db.models.deletion.CASCADE,
                    to='myapp.user',
                    related_name='posts'
                )),
            ],
            options={
                'indexes': [
                    models.Index(fields=['author'], name='post_author_idx'),
                    models.Index(fields=['published_at'], name='post_published_idx'),
                ],
            },
        ),
    ]


class Migration(migrations.Migration):
    """
    Example: Add column to existing table
    
    Laravel equivalent: php artisan make:migration add_phone_to_users_table --table=users
    """
    dependencies = [
        ('myapp', '0001_initial'),  # Previous migration
    ]
    
    operations = [
        migrations.AddField(
            model_name='user',
            name='phone',
            field=models.CharField(max_length=20, null=True, blank=True),
        ),
    ]


class Migration(migrations.Migration):
    """
    Example: Modify column
    
    Laravel equivalent: php artisan make:migration modify_users_table
    """
    dependencies = [
        ('myapp', '0002_add_phone'),
    ]
    
    operations = [
        migrations.AlterField(
            model_name='user',
            name='name',
            field=models.CharField(max_length=500),  # Change max_length
        ),
        migrations.RenameField(
            model_name='user',
            old_name='name',
            new_name='full_name',
        ),
    ]

