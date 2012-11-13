#include <stdio.h>
#include <stdlib.h>

#include "string.h"
#include "config.h"
#include "parse.h"

int main(int argc, char **argv){
  if( argc!=6 ){
    fprintf(stderr, "Usage: %s source dest common-path/ sqlite3-master-file sqlite-db-path/\nNote: not overwrite-protected on arg 'dest'! ", argv[0] );
    exit(1);
  }
  load_config();
  if ( !file_exists( argv[1] ) ) {
    fprintf(stderr, "Usage: %s source dest common-path/ sqlite3-master-file sqlite-db-path/\n%s: input source code not found\n", argv[0], argv[1] );
    exit(1);
  }
  if ( !file_exists( argv[3] ) ) {
    fprintf(stderr, "Usage: %s source dest common-path/ sqlite3-master-file sqlite-db-path/\ncommon-path: not found\n", argv[0], argv[3] );
    exit(1);
  }
  if ( !file_exists( argv[4] ) ) {
    fprintf(stderr, "Usage: %s source dest common-path/ sqlite3-master-file sqlite-db-path/\nsqlite3-master-file: not found\n", argv[0], argv[3] );
    exit(1);
  }
  if ( !file_exists( argv[5] ) ) {
    fprintf(stderr, "Usage: %s source dest common-path/ sqlite3-master-file sqlite-db-path/\nsqlite-db-path: not found\n", argv[0], argv[3] );
    exit(1);
  }
  interpret( argv[3], argv[4], argv[5], argv[1], argv[2] );
  unload_config();
  return 0;
}
