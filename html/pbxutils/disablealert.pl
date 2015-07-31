#!/usr/bin/perl

sub sendToXymon {
    use IO::Socket;
    my($server,$port,$msg) = @_ ;
    my $response;
    my $sock = new IO::Socket::INET (
            PeerAddr => $server,
            PeerPort => $port,
            Proto => 'tcp',
        );
    die "Could not create socket: $!\n" unless $sock;
    print $sock $msg;
    shutdown($sock, 1);
    while ($response=<$sock>)
    {
        print "$response";
    }
    close($sock);
}

$host = $ARGV[0];
  $port = 1984;
  $service = $ARGV[1];
  $time = $ARGV[2];
  $reason = $ARGV[3];
  $msg = 'disable ' . $service . ' ' . $time . ' ' . $reason;

sendToXymon($host, $port, $msg);
