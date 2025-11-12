"""
Django Migration Example

This file demonstrates Django migrations.
Compare to Laravel migrations (laravel-migration.php).

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
    Example: Create users table migration
    
    Laravel equivalent: database/migrations/2024_01_01_000001_create_users_table.php
    """
    initial = True

    dependencies = []

    operations = [
        migrations.CreateModel(
            name='User',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=255)),
                ('email', models.EmailField(max_length=255, unique=True)),
                ('email_verified_at', models.DateTimeField(null=True, blank=True)),
                ('password', models.CharField(max_length=255)),
                ('remember_token', models.CharField(max_length=100, null=True, blank=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
        ),
    ]


"""
Example: Add column to existing model
"""
class Migration(migrations.Migration):
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


"""
Example: Create model with foreign key
"""
class Migration(migrations.Migration):
    dependencies = [
        ('myapp', '0001_initial'),
    ]

    operations = [
        migrations.CreateModel(
            name='Post',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('title', models.CharField(max_length=255)),
                ('content', models.TextField()),
                ('published', models.BooleanField(default=False)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('user', models.ForeignKey(
                    on_delete=django.db.models.deletion.CASCADE,
                    to='myapp.user'
                )),
            ],
            options={
                'indexes': [
                    models.Index(fields=['user'], name='myapp_post_user_id_idx'),
                    models.Index(fields=['published'], name='myapp_post_published_idx'),
                ],
            },
        ),
    ]


"""
Example: Modify field
"""
class Migration(migrations.Migration):
    dependencies = [
        ('myapp', '0002_post'),
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

