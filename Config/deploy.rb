$config = {
    'application'       => 'cakepackages.com',
    'repository'        => 'git://github.com/cakephp/cakepackages.git',
    'remoteusername'    => 'deploy',
    'cake_folder'       => '/apps/production/resources',
    'cake_version'      => 'cakephp2.0.4',
    'plugin_dir'        => false,
    'servers'           => {
        'production'              => {
            'server'        => 'cakepackages.com',
            'application'   => 'cakepackages.com',
            'deploy_to'     => '/apps/production/cakepackages.com/default'
        },
        'staging'           => {
            'server'        => 'staging.cakepackages.com',
            'application'   => 'staging.cakepackages.com',
            'deploy_to'     => '/apps/staging/cakepackages.com/default'
        }
    },
    'cron_files'        => [ 'cakepackages' ]
}

# The application name. Pretty arbitrary, doesn't affect anything I think
set :application,     $config["application"]
# Where is the repository held? Depends on your application
set :repository,      $config["repository"]
# Deploy as this username
set :user,            $config["remoteusername"]
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
set :cake_folder,     $config["cake_folder"]
# Folder name of the specific cakephp version I want to use.
# This is a raw checkout straight from github
# Assumes you have this folder in the :cake_folder directory
set :cake_version,    $config["cake_version"]
# The plugin directory (relative to :cake_folder) to be deployed
set :plugin_dir,      $config["plugin_dir"]

## SSH Options
# Deploy as this username
set :ssh_options,     :username => $config["remoteusername"]
# SSH Agent forwarding, sends my personal keys for usage by git when deploying.
set :ssh_options,     :forward_agent => true

## Available Environments
task :production do
  server              $config['servers']['production']['server'], :web, :god, :cron
  set :application,   $config['servers']['production']['application']
  set :deploy_to,     $config['servers']['production']['deploy_to']
  set :branch,        :master
  set :deploy_env,    :production
end

task :staging do
  role :web,          $config['servers']['staging']['server']
  set :application,   $config['servers']['staging']['application']
  set :deploy_to,     $config['servers']['staging']['deploy_to']
  set :branch,        ENV['branch'] if ENV.has_key?('branch') && ENV['branch'] =~ /[\w_-]+/i
  set :deploy_env,    :staging
end

## Deployment tasks
namespace :deploy do
  task :start do
  end

  task :stop do
  end

  desc 'Override the original :restart'
  task :restart, :roles => :app do
    # after 'deploy:restart', 'misc:clear_cache'
  end

  desc 'Override the original :migrate'
  task :migrate do
  end

  desc <<-DESC
    Symlinks shared configuration and directories into the latest release
    Also clear persistent and model cache and sessions and symlink for usability.
  DESC
  task :finalize_update do
    before 'deploy:symlink', 'link:core', 'link:plugins', 'link:config', 'link:tmp', 'misc:rm_test', 'misc:submodule'
    after 'deploy:symlink', 'link:cron'
  end

  desc <<-DESC
    Copies over the latest release. Necessary unless we place the cake core inside releases
    For larger repositories, something different should be tried instead
  DESC
  task :symlink do
    run "rm -rf #{deploy_to}/#{current_dir} && cp -rf #{latest_release} #{deploy_to}/#{current_dir}"
  end

end

## Link tasks
namespace :link do
  desc <<-DESC
    Link the CakePHP Core
    You may need to change this to a 'cp -rf' instead of 'ln -s' depending upon your shell requirements
  DESC
  task :core do
    run "rm -rf #{deploy_to}/lib && ln -s #{cake_folder}/#{cake_version}/lib #{deploy_to}/lib"
  end

  desc 'Link the cron file'
  task :cron, :roles => :cron do
    if deploy_env == :production
      cmd = []

      $config['cron_files'].each do |cron_file|
        cmd << "sudo chown root:root #{current_path}/Config/#{cron_file}.cron"
        cmd << "sudo ln -sf #{current_path}/Config/#{cron_file}.cron /etc/cron.d/#{cron_file}"
      end

      run cmd.join(' && ')
    end
  end

  desc 'Link the CakePHP Plugins for this repository'
  task :plugins do
    if plugin_dir
      run "rm -rf #{deploy_to}/Plugin && ln -s #{cake_folder}/#{plugin_dir} #{deploy_to}/Plugin"
    end

    if deploy_env == :production
      run "cd #{current_release} && git clone git@github.com:cakephp/theme-packages.git View/Themed/Csf"
    end
  end

  desc <<-DESC
    Link the configuration files
    May fail if you are not using the asset_compress plugin
  DESC
  task :config do
    run [
      "rm -rf #{current_release}/Config/environments.php",
      "ln -s #{shared_path}/Config/environments.php #{current_release}/Config/environments.php",
      
      "if [ ! -d '#{shared_path}/webroot/cache_css' ]; then " +
          "mkdir -p #{shared_path}/webroot/cache_css && chmod -R 777 #{shared_path}/webroot/cache_css;" +
      'fi',

      "rm -rf #{current_release}/webroot/cache_css",
      "ln -s #{shared_path}/webroot/cache_css #{current_release}/webroot/cache_css",

      "if [ ! -d '#{shared_path}/webroot/cache_js' ]; then " +
          "mkdir -p #{shared_path}/webroot/cache_js && chmod -R 777 #{shared_path}/webroot/cache_js;" +
      'fi',

      "rm -rf #{current_release}/webroot/cache_js",
      "ln -s #{shared_path}/webroot/cache_js #{current_release}/webroot/cache_js",

      "rm -rf #{current_release}/webroot/uploads",
      "ln -s #{shared_path}/webroot/uploads #{current_release}/webroot/uploads",

      "rm -rf #{current_release}/webroot/files",
      "ln -s #{shared_path}/webroot/files #{current_release}/webroot/files",
    ].join(' && ')
  end

  desc 'Link the temporary directory'
  task :tmp do
    run "rm -rf #{current_release}/tmp && ln -s #{shared_path}/tmp #{current_release}/tmp"
  end

end

## Miscellaneous tasks
namespace :misc do
  desc 'Blow up all the cache files CakePHP uses, ensuring a clean restart.'
  task :clear_cache do
    # Remove absolutely everything from TMP
    run "rm -rf #{shared_path}/tmp/*"

    # Create TMP folders
    run [
      "rm -rf #{shared_path}/tmp/*",
      "rm -rf #{shared_path}/webroot/cache_css/*",
      "rm -rf #{shared_path}/webroot/cache_js/*",

      "mkdir -p #{shared_path}/tmp/cache/models",
      "mkdir -p #{shared_path}/tmp/cache/persistent",
      "mkdir -p #{shared_path}/tmp/cache/views",
      "mkdir -p #{shared_path}/tmp/sessions",
      "mkdir -p #{shared_path}/tmp/logs",
      "mkdir -p #{shared_path}/tmp/tests",

      "chmod -R 777 #{shared_path}/tmp",
    ].join(' && ')
  end

  desc 'Startup a new deployment'
  task :startup do
    # symlink the cake core folder to where we need it
    after 'misc:startup', 'link:core', 'link:plugins', 'misc:clear_cache'

    run [
      # Setup shared folders
      "mkdir -p #{shared_path}/tmp/cache/models",
      "mkdir -p #{shared_path}/tmp/cache/persistent",
      "mkdir -p #{shared_path}/tmp/cache/views",
      "mkdir -p #{shared_path}/tmp/sessions",
      "mkdir -p #{shared_path}/tmp/logs",
      "mkdir -p #{shared_path}/tmp/tests",

      "mkdir -p #{shared_path}/webroot/files",
      "mkdir -p #{shared_path}/webroot/uploads",
      "mkdir -p #{shared_path}/webroot/cache_css",
      "mkdir -p #{shared_path}/webroot/cache_js",

      # Make the TMP and Uploads folder writeable
      "chmod -R 777 #{shared_path}/tmp",
      "chmod -R 644 #{shared_path}/webroot/cache_css #{shared_path}/webroot/cache_js",
      "chmod -R 755 #{shared_path}/tmp #{shared_path}/webroot/uploads #{shared_path}/webroot/files"
    ].join(' && ')
  end

  desc 'Initialize the submodules and update them'
  task :submodule do
    run "cd #{current_release} && git submodule init && git submodule update"
  end

  desc 'Initialize the submodules and update them'
  task :rm_test do
    run "cd #{current_release} && rm -rf webroot/test.php" if deploy_env == :production
  end

  desc 'Tail the log files'
  task :tail do
    run "tail -f #{deploy_to}/logs/*.log"
  end
end

## Tasks involving assets
namespace :asset do
  desc 'Clears assets'
  task :clear do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} asset_compress clear"
  end

  desc 'Builds all assets'
  task :build do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} asset_compress build"
  end

  desc 'Builds ini assets'
  task :build_ini do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} asset_compress build_ini"
  end

  desc 'Rebuilds assets'
  task :rebuild do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} asset_compress clear"
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} asset_compress build"
  end
end

## Tasks involving migrations
namespace :migrate do
  desc 'Run CakeDC Migrations'
  task :all do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} migration run all"
  end

  desc 'Gets the status of CakeDC Migrations'
  task :status do
    run "cd #{deploy_to}/#{current_dir} && ../lib/Cake/Console/cake -env=#{deploy_env} -app #{deploy_to}/#{current_dir} migration status"
  end
end

## Tasks involving God+CakeDJJob
namespace :god do
  task :stop, :roles => :god do
    run "#{sudo} service god stop"
  end

  task :start, :roles => :god do
    run "#{sudo} service god start"
  end

  task :status, :roles => :god do
    run "#{sudo} service god status"
  end

  task :restart, :roles => :god do
    run [
      "#{sudo} service god stop",
      "#{sudo} rm /etc/god/conf.d/workers.god",
      "#{sudo} rm /etc/god/conf.d/cakephp_god.rb",
      "#{sudo} ln -s #{current_release}/Config/workers.god /etc/god/conf.d/workers.god",
      "#{sudo} ln -s #{current_release}/Config/cakephp_god.rb /etc/god/conf.d/cakephp_god.rb",
      "#{sudo} service god start"
    ].join(' && ')
  end
end