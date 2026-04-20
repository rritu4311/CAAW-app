# CAAW-app

<p align="center">
A collaborative asset management workflow application built with Laravel
</p>

## About CAAW-app

CAAW-app is a comprehensive collaborative asset management and workflow system designed to help teams organize, manage, and approve digital assets within workspaces and projects. The application provides a structured environment for file management, approval workflows, and team collaboration.

## Features

### Workspace & Project Management
- **Workspaces**: Create and manage collaborative workspaces for team projects
- **Projects**: Organize assets within projects under workspaces
- **Sharing**: Share workspaces and projects with team members via invitations
- **Member Management**: Add/remove members with role-based access control (Admin, Member, Viewer)
- **Archive**: Archive and restore workspaces and projects

### Asset Management
- **Folder Structure**: Organize assets in hierarchical folders with drag-and-drop reordering
- **File Upload**: Upload and manage various file types with preview support
- **Asset Preview**: Generate thumbnails and previews for images and documents
- **Version Control**: Upload and manage multiple versions of assets
- **Annotations**: Add annotations to assets with acknowledgment and resolution tracking

### Approval Workflows
- **Custom Workflows**: Create and configure custom approval workflows for assets
- **Workflow Templates**: Apply predefined workflow templates
- **Approval Process**: Submit assets for review, approve, reject, or request changes
- **Status Tracking**: Track asset status through the approval pipeline
- **Notifications**: Receive notifications for approval requests and actions

### Collaboration Features
- **Activity Log**: Track all activities within workspaces and projects
- **Comments**: Add and manage comments on assets
- **Notifications**: Real-time notifications for workspace invitations, project requests, and approval actions
- **Pending Approvals**: Centralized view of pending approval requests

### User Management
- **Authentication**: Secure user authentication with Laravel Breeze
- **Profile Management**: Update user profiles and notification preferences
- **Theme Toggle**: Switch between light and dark mode

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or PostgreSQL database

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd CAAW-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file and set your database credentials:
   ```
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

   For a full development experience with hot reloading:
   ```bash
   composer run dev
   ```

## Usage

1. **Register/Login**: Create an account or log in to access the application
2. **Create Workspace**: Navigate to Workspaces and create a new workspace
3. **Add Projects**: Create projects within your workspace
4. **Upload Assets**: Upload files to folders within projects
5. **Manage Workflows**: Create approval workflows and apply them to assets
6. **Collaborate**: Invite team members and collaborate on asset approvals

## Technologies Used

- **Backend**: Laravel 12.0 (PHP 8.2+)
- **Frontend**: Blade Templates, TailwindCSS, Vite
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Breeze
- **Activity Logging**: Spatie Laravel Activitylog
- **Testing**: Pest PHP

## Project Structure

```
app/
├── Http/Controllers/       # Application controllers
├── Models/                # Eloquent models
├── Http/Middleware/       # Custom middleware
database/
├── migrations/            # Database migrations
├── seeders/               # Database seeders
resources/
├── views/                 # Blade templates
├── assets/                # Frontend assets
routes/
├── web.php                # Web routes
└── auth.php               # Authentication routes
```

## Contributing

Thank you for considering contributing to CAAW-app! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `composer run test`
5. Submit a pull request

## License

The CAAW-app is open-sourced software licensed under the MIT license.
