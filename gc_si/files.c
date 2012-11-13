#include <sys/stat.h>
#include <math.h>
#include <stdbool.h>
#include <stdarg.h>

#include "string.h"
#include "files.h"
#include "mem.h"

void OUTPUT(char *fmt, ...)
{
  char buf[2048];
  va_list args;
  va_start(args, fmt);
  vsprintf(buf, fmt, args);
  append_file( "gc.log", buf );
  va_end(args);
  return;
}

bool file_exists( char *name ) {
  FILE *fp;
  fp=fopen(name,"r");
  if ( !fp ) return false;
  fclose(fp);
  return true;
}

/*
 * Advances to eol.
 */
void fread_to_eol( FILE *fp )
{
 char c = getc( fp );
 while ( c != '\n' && c != '\r' ) c = getc( fp );
 do    {	c = getc( fp );    }
 while ( c == '\n' || c == '\r' );
 ungetc( c, fp );
 return;
}

char *fread_string_eol( FILE *fp ) {
 char buf[STRING_SIZE];
 char buf2[STRING_SIZE];
 char c;
 buf[0]='\0';
 buf2[0]='\0';
 while ( !feof(fp) ) {
  c = fgetc( fp );
  fprintf( stdout, "%c", c );
  if ( feof(fp) ) { ungetc(c,fp); break; }
  if ( c=='\r' ) continue;
  if ( c=='\n' ) {
   fprintf( stdout, "%s", buf2 );
   return strdup(buf2); }
  strcpy(buf,buf2);
  snprintf( buf2, STRING_SIZE, "%s%c", buf, c );
 }
 fprintf( stdout, "%s", buf2 );
 return strdup( buf2 );
}

/*
 * Returns a list of lines
 */
STRING_LIST *fread_file( char *filename ) {
     FILE *fp=fopen( filename, "r" );
     STRING_LIST *slist=NULL;
     STRING_LIST *last=NULL;

     if (fp == NULL) { OUTPUT("File not found: %s\n", filename); return NULL; }
     
     while( !feof(fp) && !ferror(fp) )
     {
        STRING_LIST *snode = new_string_list( );
        snode->str=fread_string_eol(fp);
        if ( snode->str == NULL ) { free_string_list(snode); break; }
        if ( last != NULL ) last->next = snode;
        else slist=snode;
        last=snode;
     }
     
     fclose(fp);
     return slist;
}

int new_folder( char *path ) {
    int status = mkdir(path,770); 
	OUTPUT( "new_folder(\"%s\"); ", path);
#if defined(NEVER)
    switch ( status ) {
   case EACCES:             OUTPUT( "Search permission is denied on a component of the path prefix, or write permission is denied on the parent directory of the directory to be created.\n" );        break;
   case EEXIST:             OUTPUT( "The named file exists.\n" );        break;
  //  case ELOOP: OUTPUT( "new_folder(\"%s\"); ", path);
    //        OUTPUT( "A loop exists in symbolic links encountered during resolution of the path argument.\n",0);
      //  break;
   case EMLINK:             OUTPUT( "The link count of the parent directory would exceed {LINK_MAX}.\n" );        break;
case ENAMETOOLONG:          OUTPUT( "The length of the path argument exceeds {PATH_MAX} or a pathname component is longer than {NAME_MAX}." );        break;
   case ENOENT:             OUTPUT( "A component of the path prefix specified by path does not name an existing directory or path is an empty string.\n" );        break;
   case ENOSPC:             OUTPUT( "The file system does not contain enough space to hold the contents of the new directory or to extend the parent directory of the new directory.\n" );        break;
  case ENOTDIR:             OUTPUT( "A component of the path prefix is not a directory.\n" );        break;
    case EROFS:             OUTPUT( "The parent directory resides on a read-only file system.\n" );        break;
        case 0: OUTPUT( "new_folder(\"%s\"); successful\n", path); break;
    default: 
         //    OUTPUT( "Unknown error, possibly ELOOP; already exists or read-only.\n", 0 );
        break;
    }
#else
  if ( status == 0 ) OUTPUT( "new_folder: created successfully.\n" );
  else OUTPUT( "new_folder: unsuccessful errno: %d\n", status );
#endif
    return status;
}

int file_as_string( char *filename, char **result) 
{ 
	int size = 0;
	FILE *f = fopen(filename, "rb");
	if (f == NULL) 
	{ 
		*result = NULL;
		return -1; // -1 means file opening fail 
	} 
	fseek(f, 0, SEEK_END);
	size = ftell(f);
	fseek(f, 0, SEEK_SET);
	*result = (char *)calloc(size+1,sizeof(char));
	if (size != fread(*result, sizeof(char), size, f)) 
	{ 
		free(*result);
		return -2; // -2 means file reading fail 
	} 
	fclose(f);
	(*result)[size] = 0;
	return size;
}

void string_as_file(  char *content, char *filename ) {
 FILE *pFile=fopen(filename,"w");
 if ( pFile ) {
  fwrite (content,sizeof(char),strlen(content),pFile);
  fclose(pFile);
 } else printf( "Error!  Cannot write '%s'", filename );
}
