import RPi.GPIO as GPIO, time

GPIO.setmode( GPIO.BCM )
GPIO.setwarnings( False )

def ledMode( PiPin, mode ):
  GPIO.setup( PiPin, GPIO.OUT )
  GPIO.output( PiPin, mode )

  return


ledMode( 14, GPIO.HIGH )
time.sleep( 1 )
ledMode( 15, GPIO.HIGH )
time.sleep( 1 )
ledMode( 18, GPIO.HIGH )
time.sleep( 1 )

ledMode( 14, GPIO.LOW )
time.sleep( 1 )
ledMode( 15, GPIO.LOW )
time.sleep( 1 )
ledMode( 18, GPIO.LOW )
time.sleep( 1 )

