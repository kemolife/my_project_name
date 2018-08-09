# server-based syntax
# ======================
# Defines a single server with a list of roles and multiple properties.
# You can define all roles on a single server, or split them:

server '45.40.133.119', user: 'root', roles: %w{app web db}

# role-based syntax
# ==================

# Defines a role with one or multiple servers. The primary server in each
# group is considered to be the first unless any  hosts have the primary
# property set. Specify the username and a domain or IP for the server.
# Don't use `:all`, it's a meta role.

# role :app, %w{deploy@example.com}, my_property: :my_value
# role :web, %w{user1@primary.com user2@additional.com}, other_property: :other_value
# role :db,  %w{deploy@example.com}

# Configuration
# =============
# You can set any configuration variable like in config/deploy.rb
# These variables are then only loaded and set in this stage.
# For available Capistrano configuration variables see the documentation page.
# http://capistranorb.com/documentation/getting-started/configuration/
# Feel free to add new variables to customise your setup.
set :deploy_to, '/var/www/businesslistings.cubeonline.com.au/'
# Default branch is :master
set :branch, :'master'

#Custom SSH Options
# ==================
# You may pass any option but keep in mind that net/ssh understands a
# limited set of options, consult the Net::SSH documentation.
# http://net-ssh.github.io/net-ssh/classes/Net/SSH.html#method-c-start
#
# Global options
# --------------
#  set :ssh_options, {
#    keys: %w(/home/rlisowski/.ssh/id_rsa),
#    forward_agent: false,
#    auth_methods: %w(password)
#  }
#
# The server-based syntax can be used to override options:
# ------------------------------------
# server 'example.com',
#   user: 'user_name',
#   roles: %w{web app},
#   ssh_options: {
#     user: 'user_name', # overrides user setting above
#     keys: %w(/home/user_name/.ssh/id_rsa),
#     forward_agent: false,
#     auth_methods: %w(publickey password)
#     # password: 'please use keys'
#   }

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

  after :publishing, :supervisor do
    on roles(:app, :web) do
      within release_path do
        execute :sudo , 'service supervisor restart'
      end
    end
  end

  after :permissions, :fpm do
    on roles(:app, :web) do
      within release_path do
        execute :sudo, 'service php7.1-fpm', 'restart'
      end
    end
  end
  end
