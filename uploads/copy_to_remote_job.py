# This is a file to copy files from copy_to_remote folders, and remove them after done.
# Add this line to crontab -e, so the py script will be ran every munite:
#     * * * * * /usr/bin/python /var/www/html/uploads/copy_to_remote_job.py
# A very simple log will be stored in /tmp/copy_to_remote_log.txt
# Also, if detected failure and restarted, will send an email to administrators
import commands
import subprocess 
import os
#import paramiko
import smtplib
from time import gmtime, strftime
from email.mime.text import MIMEText

copy_folder = '/var/www/html/uploads/copy_to_remote'
log = '/tmp/copy_to_remote_log.txt'
config_path = '/home/ubuntu/tag_config'

# Send email and log errors
def write_to_log(error):
  # Send the informing email via our own SMTP server
  sender = "support@tagtalk.co"
  receiver = ["s810011@gmail.com", "s810434@gmail.com"]
  msg = MIMEText(strftime("[%Y-%m-%d %H:%M:%S] ", gmtime()) + "Error when coping to remote: " + error)
  msg['Subject'] = "TagTalk copy files to remote error";
  msg['From'] = sender
  msg['To'] = ",".join(receiver)
  smtp = smtplib.SMTP('localhost')
  smtp.sendmail(sender, receiver, msg.as_string())
  smtp.quit()
  # Write to error log
  with open(log, "a+") as f:
    f.write(strftime("[%Y-%m-%d %H:%M:%S] ", gmtime()) + error + "\n")

# Copy by scp 
def copy_by_scp(config):
  scp_command = "scp -q -i " + config['remote_key_dir'] + " " + copy_folder + "/* " + config['remote_user'] + '@' + config['remote_server'] + ":/var/www/html/uploads/uploads"
  output = subprocess.check_output(scp_command, shell=True)
  if not output.strip():
    subprocess.check_output('rm -f ' + copy_folder + '/*', shell=True)
  else:
    write_to_log("scp output is not empty: " + str(output))

# copy by sftp - super slow, do not use.
def copy_by_sftp(config):
  key = paramiko.RSAKey.from_private_key_file(config['remote_key_dir'])
  ssh = paramiko.SSHClient()
  ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
  print "connecting"
  ssh.connect(hostname = config['remote_server'], username = config['remote_user'], pkey = key)
  print "connected"
  sftp = ssh.open_sftp()
  file_list = os.listdir("/var/www/html/uploads/copy_to_remote/")
  print str(file_list)
  for file_name in file_list:
    print "process " + file_name + "\n"
    sftp.put('/var/www/html/uploads/copy_to_remote/' + file_name, '/var/www/html/uploads/uploads/' + file_name)
  ssh.close()



# If the script is already running, do not overrun
ps_out = commands.getstatusoutput("ps -o cmd= -C scp")[1]
print ps_out
if ps_out.find("copy_to_remote") != -1:
  write_to_log("copy_to_remote_job.py overrun.")
  quit()

# If there are files to be copied to remote, then do the job 
if os.listdir(copy_folder) == []:
  quit()

# Start coping all files in copy_to_remote to remote server
config = {}
try:
  with open(config_path) as myfile:
    lines = myfile.read().splitlines()
    for line in lines:
      name, var = line.partition("=")[::2]
      config[name.strip()] = var.strip()
except Exception as e:
  write_to_log(str(e))
  pass
try:
  copy_by_scp(config)
except Exception as e:
  write_to_log(str(e))
  pass

