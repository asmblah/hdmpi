# HDMpI

[![Build Status](https://github.com/asmblah/hdmpi/workflows/CI/badge.svg)](https://github.com/asmblah/hdmpi/actions?query=workflow%3ACI)

KVM & HDMI capture with TypeScript and PHP using [Uniter][].

> **Warning** this is highly experimental and not yet ready for real world usage.

Uses off-the-shelf HDMI-to-IP hardware and Raspberry Pis to (hopefully) provide low-latency KVM functionality.

## Setup

### Set up Pi to join both default VLAN & your HDMI-to-IP VLAN
https://engineerworkshop.com/blog/raspberry-pi-vlan-how-to-connect-your-rpi-to-multiple-networks/

## Usage

NB: Replace `192.168.1.100` with your receiver Pi's IP.

```shell
# Server
ffmpeg -r 25 -analyzeduration 32 -probesize 512 -pixel_format yuvj422p -f s32be -ar 48000 -ac 2 -thread_queue_size 16 -i /tmp/hdmpi_audio_fifo -f mjpeg -thread_queue_size 16 -i /tmp/hdmpi_video_fifo -preset ultrafast -tune zerolatency -fflags +genpts -c:v libx264 -c:a aac -b:v 300k -b:a 56k -bufsize 300k -f flv - | nc 192.168.1.100 5010

# Client (video)
nc -l 5010 | ffmpeg -i - -c:v mjpeg -an -f rawvideo video.mjpeg.raw
nc -l 5010 | ffmpeg -i - -map 0:0 -an -c:v mjpeg -f data video.mjpeg.raw

# Client (audio)
nc -l 5010 | ffmpeg -i - -map 0:1 -vn -c:a pcm_s32be -ar 48000 -ac 2 -f data audio.s32be.raw
# Playback test
ffplay -i audio.s32be.raw -f s32be -ar 48000 -ac 2

# Client (audio & video)
nc -l 5010 | ffmpeg -i - -map 0:0 -an -c:v mjpeg -f data video.mjpeg.raw \
  -map 0:1 -vn -c:a pcm_s32be -ar 48000 -ac 2 -f data audio.s32be.raw
```

## See also
- [PiKVM][]
- [Lenkeng373][]

[Lenkeng373]: https://github.com/toru173/Lenkeng373
[PiKVM]: https://pikvm.org/
[Uniter]: https://github.com/asmblah/uniter
