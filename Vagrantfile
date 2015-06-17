Vagrant.configure("2") do |config|

  ip          = '10.33.36.16'
  project     = 'rootcanal'
  path        = "/var/www/sites/#{project}.dev"

  config.vm.box     = "craychee/drupal-ubuntu"

  config.vm.synced_folder ".", "/vagrant", :disabled => true
  config.vm.synced_folder ".", path, :nfs => true
  config.vm.hostname = "#{project}.dev"
  config.vm.network :private_network, ip: ip

  config.ssh.forward_agent = true

  config.vm.provision :shell, inline: <<SCRIPT
  set -ex
  su vagrant -c 'cd #{path} && composer install;'
SCRIPT
end
