IMPORTANT:  THIS PROJECT IS NOT YET READY TO USE.  IT DOES NOT HAVE ALL THE REQUIRED FILES.  CHECK BACK LATER (~Oct 2013)

gc
==

Gudagi Web Interpreter (Open Source)

Gudagi Web Interpreter implements a C interpreter which converts "GC markup" into .php
enabled web files, automatically including appropriate javascript and other ancillary
code that is useful for quickly building web functionality.

In its current state, basic blogging, e-commerce and jQuery integration features are
ready.

When looked at like a PHP utility function library, this package contains useful
implementations to simplify database integration.  

When viewed as an interpreted website building language, GC is partially completed
but has the basic functionality which can be extended to your needs and improved by
adding functionality unique to your client needs.  As you extend the language, you
will be encapsulating raw web functionality to re-use on other sites.

An example candidate addition to "gc" would be some compartmentalized functionality,
such as a specific widget, which can be wrapped and programmed with GC.

GC is essentially a configuration and data sourcing language, which provides simple
markup and can be used to launch and re-launch both development and production
sites effectively.

One work-flow would be to provide designers with a way to manipulate the underlying
PHP using the GC language, in a way similar to ColdFusion but with less reliance
on query writing.  Your PHP developers would develop new GC objects using widgetry
wrapped in a GC object (See /common/ directory which includes the packaged objects
that capture a broad range of web development topics).


Directory Setup
---------------

GC sites are stored online, and a duplicate of the "common" directory sits off
the web root, /var/www/sites/common/ for instance, where sites are then deployed
to unique folders inside /var/www/sites/ (ie: /var/www/sites/site0001 )

Your databases should live in /sqlite/ (off the directory root) but this could
be moved or symlinked elsewhere to another offline area.

A tmpfs (ramdisk) should be deployed at /instances/ which provides a fast
place for the interpreter to export data before it is moved to another location
for longer-term storage.

The GC interpreter (built with C) by default sits in /gc/ in the directory root,
though this directory can be changed to any other offline area.  The offline area
provides both security for the GC interpreter's core files, but also distinguishes
it from other frameworks which rely solely on online code.

The interpreter takes .gc and .html/.htm files marked up with GC, that is then
deployed to a target online website directory.

Auth
----

Since GC was written for a site deployment service, the structure of the database
is provided in database.sql (included in the distribution) and uses a custom
authentication profile such that any user logged into the site is logged into
any site deployed through GC.  This can be manipulated by making changes to the
provided custom authentication system.   Of note, the authentication method uses
minimal client-side cookies and provides granular examination of usage.  However,
it is not an all-encompassing implementation, and could be extended to include
other metrics and usage tracking information.

Other features either planned or implemented include:
 - ability for rapid deployment of UGC sites
 - ability for uploaded media to be stored in a centralized repository or NSF*
 - ability for cross-site sharing of uploaded content
  (for example sharing your royalty free content library with all of your customers)

* This is a part of the broader Gudagi project, which will open source the
  Gudagi Media Engine(tm), an associated tool that implements many of these
  features and includes ffmpeg video transcoding, media cataloging, etc

Possible future features or branches:
 - a visual tool for editing and creating content that deploys GC markup

SQLite and Scaling
------------------

You can easily scale SQLite, so this was chosen as the database system.  SQLite
is also fast and reliable, and isn't bogged down by legacy features.  You could
certainly modify this aspect, but there is no switch between PDO database systems
like there are in other frameworks.  If you need to disperse SQLite across a
networked file system, you can always find/invent a cataloging method to make
individual files smaller, and across a wider number of storage locations and
devices.
