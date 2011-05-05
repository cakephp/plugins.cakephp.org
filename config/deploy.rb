# The application name. Pretty arbitrary, doesn't affect anything I think
set :application,     "cakepackages.com"
# Where is the repository held? Depends on your application
set :repository,      "git://github.com/josegonzalez/cakepackages.git"
# Deploy as this username
set :user,            "deploy"
# Do NOT use sudo by default. Helps with file permissions. You can still
# manually sudo by prepending #{sudo} to run commands
set :use_sudo,        false

# SCM Settings
# Use git to deploy. You can also set this to 'svn'
set :scm,             :git
# Only deploy the master branch
set :branch,          "master"
# Keep Git quiet
set :scm_verbose,     false

## Deploy Settings
# Deploy via a remote repository cache. In git's case, it 
# does a "git fetch" on the remote cache before moving it into place
set :deploy_via,      :remote_cache
# Overriding my 'current' directory to public, as that's how I roll
set :current_dir,     "public"

## Deploy Specific settings
# The folder holding all of my CakePHP core stuff,
# like plugins and the individual cores
set :cake_folder,     "/apps/production/resources"
# Folder name of the specific cakephp version I want to use.
# This is a raw checkout straight from github
set :cake_version,    "cakephp1.3"
# The plugin directory (relative to :cake_folder) to be deployed
set :plugin_dir,      "cakephp-plugins"

## SSH Options
# SSH Agent forwarding, sends my personal keys for usage by git when deploying.
set :ssh_options,     :forward_agent => true

## Available Environments
task :prod do
  server "cakepackages.com", :web, :god
  set :application, "cakepackages.com"
  set :deploy_to, "/apps/production/cakepackages.com/default"
  set :branch, :master
end

task :dev do
  role :web, "dev.cakepackages.com"
  set :application, "dev.cakepackages.com"
  set :deploy_to, "/apps/production/cakepackages.com/dev"
  set :branch, ENV['branch'] if ENV.has_key?('branch') && ENV['branch'] =~ /[\w_-]+/i
end

#Deployment tasks
namespace :deploy do
  task :start do
  end

  task :stop do
  end

  desc "Override the original :restart"
  task :restart, :roles => :app do
    # after "deploy:restart", "misc:clear_cache"
  end

  desc "Override the original :migrate"
  task :migrate do
  end

  desc <<-DESC
    Symlinks shared configuration and directories into the latest release
    Also clear persistent and model cache and sessions and symlink for usability.
  DESC
  task :finalize_update do
    before "deploy:symlink", "link:core", "link:plugins", "link:config", "link:tmp", "misc:submodule"
    after "deploy:symlink", "misc:index"
  end

  desc <<-DESC
    Copies over the latest release. Necessary unless we place the cake core inside releases
    For larger repositories, something different should be tried instead
  DESC
  task :symlink do
    run "rm -rf #{deploy_to}/#{current_dir} && cp -rf #{latest_release} #{deploy_to}/#{current_dir}"
  end

end

namespace :link do
  desc "Link the CakePHP Core"
  task :core do
    run "rm -rf #{deploy_to}/cake && cp -rf #{cake_folder}/#{cake_version}/cake #{deploy_to}/cake"
  end

  desc "Link the CakePHP Plugins for this repository"
  task :plugins do
    run "rm -rf #{deploy_to}/#{plugin_dir} && cp -rf #{cake_folder}/#{plugin_dir} #{deploy_to}/plugins"
  end

  desc "Link the configuration files"
  task :config do
    cmd = [
      "rm -rf #{current_release}/config/core.php",
      "ln -s #{shared_path}/config/core.php #{current_release}/config/core.php",

      "rm -rf #{current_release}/config/database.php",
      "ln -s #{shared_path}/config/database.php #{current_release}/config/database.php",

      "rm -rf #{current_release}/config/bootstrap.php",
      "ln -s #{shared_path}/config/bootstrap.php #{current_release}/config/bootstrap.php",

      "rm -rf #{current_release}/webroot/cache_css",
      "ln -s #{shared_path}/webroot/cache_css #{current_release}/webroot/cache_css",

      "rm -rf #{current_release}/webroot/cache_js",
      "ln -s #{shared_path}/webroot/cache_js #{current_release}/webroot/cache_js",
    ]

    run cmd.join(' && ')
  end

  desc "Link the temporary directory"
  task :tmp do
    run "rm -rf #{current_release}/tmp && ln -s #{shared_path}/tmp #{current_release}/tmp"
  end

end

namespace :misc do
  desc "Blow up all the cache files CakePHP uses, ensuring a clean restart."
  task :clear_cache do
    # Remove absolutely everything from TMP
    run "rm -rf #{shared_path}/tmp/*"

    # Create TMP folders
    run "mkdir -p #{shared_path}/tmp/{cache/{models,persistent,views},sessions,logs,tests}"
  end

  desc "Build the search index"
  task :index do
    run "cd #{deploy_to}/#{current_dir} && ../cake/console/cake -app #{deploy_to}/#{current_dir} build_search_index Package -interactive false -quiet true"
  end

  desc "Startup a new deployment"
  task :startup do
    # symlink the cake core folder to where we need it
    after "misc:startup", "link:core", "link:plugins"

    # Setup shared folders
    run "mkdir -p #{shared_path}/tmp/{cache/{data,models,persistent,views},sessions,logs,tests};"
    run "mkdir -p #{shared_path}/webroot/uploads"
    run "mkdir -p #{shared_path}/webroot/cache_css"
    run "mkdir -p #{shared_path}/webroot/cache_js"

    # Make the TMP and Uploads folder writeable
    run "chmod -R 644 #{shared_path}/webroot/cache_css #{shared_path}/webroot/cache_js"
    run "chmod -R 755 #{shared_path}/tmp #{shared_path}/webroot/uploads"
  end

  desc "Initialize the submodules and update them"
  task :submodule do
    run "cd #{current_release} && git submodule init && git submodule update"
  end

  desc "Tail the log files"
  task :tail do
    run "tail -f #{deploy_to}/logs/*.log"
  end

  task :god_stop, :roles => :god do
    run "#{sudo} service god stop"
  end

  task :god_start, :roles => :god do
    run "#{sudo} service god start"
  end

  task :god_restart, :roles => :god do
    run "#{sudo} service god stop"
    run "#{sudo} rm /etc/god/conf.d/workers.god"
    run "#{sudo} rm /etc/god/conf.d/cakephp_god.rb"
    run "#{sudo} ln -s #{current_release}/config/workers.god /etc/god/conf.d/workers.god"
    run "#{sudo} ln -s #{current_release}/config/cakephp_god.rb /etc/god/conf.d/cakephp_god.rb"
    run "#{sudo} service god start"
  end
end