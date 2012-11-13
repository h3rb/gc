#include <stdio.h>
#include <stdlib.h>

#include "string.h"
#include "parse.h"

int main(int argc, char **argv){
  if( argc!=3 ){
    fprintf(stderr, "Usage: %s /path/to/index.gc device_file\nWhere: device_file contains HTTP_USER_AGENT and HTTP_ACCEPT\nOutputs: redirection\n\n", argv[0] );
    exit(1);
  }
  if ( !file_exists( argv[1] ) || !file_exists( argv[2] ) ) {
    fprintf(stderr, "Usage: %s /path/to/index.gc device_file\nWhere: device_file contains HTTP_USER_AGENT and HTTP_ACCEPT\nOutputs: redirection\n\n", argv[0] );
    exit(1);
  }
  interpret( argv[1], argv[2] );
  return 0;
}
