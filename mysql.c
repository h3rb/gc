// GC GudaCode interpreter

#include <unistd.h>
#include <mysql.h>
#include <stdio.h>

int main() {
   MYSQL *conn;
   MYSQL_RES *res;
   MYSQL_ROW row;

   char *server = "fullgrownmonkey.com";
   char *user = "fullgrow";
   char *password = "nOECUG2C2i"; /* set me first */
   char *database = "fullgrow_thebooks";

   printf( "initializing ... ");
   conn = mysql_init(NULL);

   printf( "connecting ... ");
   /* Connect to database */
   if (!mysql_real_connect(conn, server,
         user, password, database, 0, NULL, 0)) {
      fprintf(stderr, "%s\n", mysql_error(conn));
      exit(1);
   }

   printf( "querying ... ");
   /* send SQL query */
   if (mysql_query(conn, "show tables")) {
      fprintf(stderr, "%s\n", mysql_error(conn));
      exit(1);
   }

   res = mysql_use_result(conn);

   /* output table name */
   printf("MySQL Tables in mysql database:\n");
   while ((row = mysql_fetch_row(res)) != NULL)
      printf("%s \n", row[0]);

   printf( "closing..." );
   /* close connection */
   mysql_free_result(res);
   mysql_close(conn);
}



