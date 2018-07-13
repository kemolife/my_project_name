set :application, 'dashboard_instances'
set :repo_url, 'git@github.com:gruzik-igor/DashboardInstances.git'

# Default branch is :master
# ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp

# Default deploy_to directory is /var/www/my_app_name
# set :deploy_to, '/var/www/my_app_name'

# Default value for :scm is :git
# set :scm, :git

# Default value for :format is :pretty
# set :format, :pretty

# Default value for :log_level is :debug
set :log_level, :info

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
set :linked_files, fetch(:linked_files, []).push('app/config/parameters.yml')

# Default value for linked_dirs is []
set :linked_dirs, fetch(:linked_dirs, []).push('vendor', 'web/files')

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
set :keep_releases, 3

namespace :deploy do

  before :publishing, :install do
    on roles(:app, :web) do
      within release_path do
        execute :composer, 'install', '--no-interaction', '--optimize-autoloader'
      end
    end
  end

  before :publishing, :migrate do
    on roles(:app, :web) do
      within release_path do
        execute :php, 'bin/console', 'doctrine:migrations:migrate', '--no-interaction'
      end
    end
  end

  after :publishing, :permissions do
    on roles(:app, :web) do
      within release_path do
        execute :sudo , 'chown www-data:www-data', '-R', '.'
      end
    end
  end

  after :permissions, :fpm do
    on roles(:app, :web) do
      within release_path do
        execute :sudo, 'service php7.2-fpm', 'restart'
      end
    end
  end
  end
