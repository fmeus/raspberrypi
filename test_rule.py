import pushover
from Rules import Rules

rf = Rules( 'localhost', 'rpi', 'rpi', 'sensordata' )
rf.run_rule( 1 )
pushover.send_notification( rf.getDescription(), rf.getOutput(), None, None )
