"""
Django Management Command Examples

This file demonstrates Django management commands.
Compare to Laravel's Artisan commands (artisan-commands.php).

To create a management command:
1. Create management/commands/ directory in your app
2. Create command_name.py file
3. Extend BaseCommand class
"""

from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model

User = get_user_model()


class Command(BaseCommand):
    """
    Example: Django management command to list users
    
    Laravel equivalent: app/Console/Commands/ListUsers.php
    """
    help = 'Display a list of users in a table format'

    def add_arguments(self, parser):
        parser.add_argument(
            '--limit',
            type=int,
            default=10,
            help='Number of users to display',
        )

    def handle(self, *args, **options):
        limit = options['limit']
        
        # Fetch users from database
        users = User.objects.all()[:limit]
        
        if not users.exists():
            self.stdout.write(self.style.WARNING('No users found.'))
            return
        
        # Display header
        self.stdout.write(self.style.SUCCESS('\nUsers:'))
        self.stdout.write('-' * 80)
        self.stdout.write(f"{'ID':<10} {'Name':<30} {'Email':<30} {'Created At':<20}")
        self.stdout.write('-' * 80)
        
        # Display users
        for user in users:
            self.stdout.write(
                f"{user.id:<10} {user.name:<30} {user.email:<30} "
                f"{user.created_at.strftime('%Y-%m-%d %H:%M:%S'):<20}"
            )
        
        self.stdout.write('-' * 80)
        self.stdout.write(self.style.SUCCESS(f'Displayed {users.count()} user(s).'))


"""
Example: Command with progress bar (using tqdm)
"""
try:
    from tqdm import tqdm
    
    class ProcessUsers(BaseCommand):
        help = 'Process users with progress bar'
        
        def handle(self, *args, **options):
            users = User.objects.all()
            
            for user in tqdm(users, desc='Processing users'):
                # Process user...
                import time
                time.sleep(0.1)  # Simulate work
            
            self.stdout.write(self.style.SUCCESS('All users processed!'))
except ImportError:
    # tqdm not installed, use simple counter
    class ProcessUsers(BaseCommand):
        help = 'Process users with progress bar'
        
        def handle(self, *args, **options):
            users = User.objects.all()
            total = users.count()
            
            for i, user in enumerate(users, 1):
                # Process user...
                import time
                time.sleep(0.1)  # Simulate work
                self.stdout.write(f'Processing {i}/{total}...', ending='\r')
            
            self.stdout.write(self.style.SUCCESS('\nAll users processed!'))


"""
Example: Command with questions/interactive input
"""
class CreateUser(BaseCommand):
    help = 'Create a new user interactively'
    
    def handle(self, *args, **options):
        name = input('What is the user\'s name? ')
        email = input('What is the user\'s email? ')
        
        confirm = input('Do you want to create this user? (yes/no): ')
        if confirm.lower() != 'yes':
            self.stdout.write(self.style.WARNING('User creation cancelled.'))
            return
        
        try:
            user = User.objects.create(
                name=name,
                email=email,
            )
            self.stdout.write(
                self.style.SUCCESS(f'User created successfully! ID: {user.id}')
            )
        except Exception as e:
            self.stdout.write(
                self.style.ERROR(f'Failed to create user: {str(e)}')
            )

