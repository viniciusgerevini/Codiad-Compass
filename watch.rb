#
# Copyright (c) Vinicius Gerevini, distributed
# as-is and without warranty under the MIT License.
# See http://opensource.org/licenses/MIT for more information.
# This information must remain intact.
#

path = ARGV[0]
log_file = ARGV[1]

if path == nil
	puts "Path not informed"
	exit -1
end

if log_file == nil
	puts "Logfile not informed"
	exit -1
end

# Execute watch
pid = `compass watch "#{path}" > "#{log_file}" 2>&1 & echo $!`

last_contact = Time.now

#15
Signal.trap("TERM") do
    `kill -9 #{pid}`
    exit 0
end

# 10
Signal.trap("USR1") do
    last_contact = Time.now
end

while true do
	sleep(15)

	if (Time.now - last_contact) / 60 > 2 #Verifies if last check was more than 2 minutes
		`kill -9 #{pid}`
		exit 0
	end 
end