#include <stdio.h>
#include <stdlib.h>
#include "sqlite3.h"

char dbname[1024];
sqlite3 *db;
char *zErrMsg = 0;
int rc;
   
static int callback(void *NotUsed, int argc, char **argv, char **azColName){
  int i;
  for(i=0; i<argc; i++){
    printf("%s = %s\n", azColName[i], argv[i] ? argv[i] : "NULL");
  }
  printf("\n");
  return 0;
}

void init_sql( char *dbn ) {
 sprintf( dbname, "%s", dbn );
 rc = sqlite3_open(dbn, &db);
 if( rc ){
  fprintf(stderr, "Can't open database: %s\n", sqlite3_errmsg(db));
  sqlite3_close(db);
  exit(1);
 }
}

void query( char *sql ) {   
 rc = sqlite3_exec(db, sql, callback, 0, &zErrMsg);
 if( rc!=SQLITE_OK ){
  fprintf(stderr, "SQL error: %s\n", zErrMsg);
  sqlite3_free(zErrMsg);
 }
}

void close_sql() {
 sqlite3_close(db);
}