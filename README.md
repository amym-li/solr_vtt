# Solr VTT (WIP)

The goal of this module is to provide a method of structurally indexing WebVTT files in Apache Solr using Search API.

- [Indexing Files as Fulltext](#indexing-files-as-fulltext)
  - [Using File Extractor](#using-file-extractor)
  - [Using Search API Attachments and Patch](#using-search-api-attachments-and-patch)
  - [Using Search API Attachments and Rendered HTML](#using-search-api-attachments-and-rendered-html)
- [Indexing Child Documents in Solr](#indexing-child-documents-in-solr)
  - [Search API Processor Demo](#search-api-processor-demo)
  - [Curl Demo](#curl-demo)
  - [Example in Solr](#example-in-solr)
  - [Block Join Query](#block-join-query)
  - [Additional Resources for Nested Documents](#additional-resources-for-nested-documents)

## Indexing Files as Fulltext

These methods assume [Search API Solr](https://www.drupal.org/project/search_api_solr) is installed and fully configured.

[Search API Attachments](https://www.drupal.org/project/search_api_attachments) can be used to index media belonging to nodes, but doesn't really provide support for indexing fields belonging to the media belonging to the node. The following methods allow you to index file content regardless of the level of nesting.


### Using File Extractor

1. Install the [File Extractor](https://www.drupal.org/project/file_extractor) module. This is a fork of [Search API Attachments](https://www.drupal.org/project/search_api_attachments).
1. Go to `/admin/config/media/file-extractor`, and configure settings.
1. Go to `/admin/config/search/search-api/index/my-index/fields/add/nojs`. Add fields containing files that need to be indexed.
    - Indexing a VTT file attached to a media belonging to the node, add: `Content » Repository Item Media » Media » Ableplayer Media Caption » Media » File » File » File extractor: extracted file`
    - Indexing documents (e.g. PDFs), add: `Content » Repository Item Media » Media » Document » File » File extractor: extracted file`

**End result in solr will look something like:** 
```
"tm_X3b_en_extracted_file":["WEBVTT 1 00:00:31.488 --> 00:00:46.847 hi 2 00:00:46.848 --> 00:00:57.343 bye"]
```


### Using Search API Attachments and Patch

1. Install [Search API Attachments](https://www.drupal.org/project/search_api_attachments) and apply the patch from this [issue](https://www.drupal.org/project/search_api_attachments/issues/3008580#comment-14287351).
1. Go to `/admin/config/search/search_api_attachments`. Set the extraction method to `Solr Extractor`. Under `Solr Extractor configuration`, select your Solr server.
1. Go to `/admin/config/search/search-api/index/my-index/fields/add/nojs`. Select fields containing the files that need to be indexed and save.
    - Indexing a VTT file attached to a media belonging to the node, add: `Content » Repository Item Media » Media » Ableplayer Media Caption » Media » File » File » Search API attachments: extracted file`
    - Indexing documents (e.g. PDFs), add: `Content » Repository Item Media » Media » Document » File » Search API attachments: extracted file`

**End result in solr will look something like:**
```
"tm_X3b_en_attachments_extracted_file":["WEBVTT 1 00:00:31.488 --> 00:00:46.847 hi 2 00:00:46.848 --> 00:00:57.343 bye"]
```

This approach is essentially the same as the File Extractor method but a patch is required.


### Using Search API Attachments and Rendered HTML

The instructions for this method was detailed originally in this [issue](https://www.drupal.org/project/search_api_attachments/issues/2844979).

1. Install [Search API Attachments](https://www.drupal.org/project/search_api_attachments). 
1. Go to `/admin/config/search/search_api_attachments`. Set the extraction method to `Solr Extractor`. Under `Solr Extractor configuration`, select your Solr server.
1. Create a new view mode for your media type.
    1. Go to `/admin/structure/display-modes/view`. Under `Media`, add a view mode called `Search Index`.
    1. Go to `Structure > Media types > Edit Able Player Caption > Manage display`. Under `Custom display settings`, select `Search Index` and save.
    1. Select the new `Search Index` tab, and set the format of the `Able Player Caption` field to `Text extracted from attachment` (this formatter is provided by Search API Attachments).
    1. Configure the field settings if needed. **Note:** may want to avoid setting the condition to `Hide when Media type is empty` -- this didn’t work when I tested it but that might have been a configuration issue.
    - To index PDFs, repeat the same process using the `Document` media type instead of `Able Player Caption`.
1. Create a new view mode for your content type.
    1. Go to `/admin/structure/display-modes/view`. Under `Content`, add a view mode called `Search Index`.
    1. Go to `Structure > Content types > Repository Item > Manage display`. Under `Custom display settings`, select `Search Index` and save.
    1. Select the new `Search Index` tab. Set the format of the `Repository Item Media` field to `Rendered entity`, and in the settings, set the view mode to `Search Index`.
1. Go to `/admin/config/search/search-api/index/my-index/fields/add/nojs`. Select `Rendered HTML output` and save. Edit the field and set the view mode for each content type to `Search Index`.


**The end result will look something like this in Solr:**

VTTs:
```
"tm_X3b_en_rendered_item":[“By admin , 18 May, 2023 Repository Item Media WEBVTT 1 00:00:31.488 --> 00:00:46.847 hi 2 00:00:46.848 --> 00:00:57.343 bye. Fri, 05/19/2023 - 19:25 Extent 1 item"]
```

PDFs:
```
"tm_X3b_en_rendered_item":["pdf title","By admin , 19 May, 2023 Repository Item Media Document TEXT EXTRACTED FROM PDF FOUND HERE Extent 1 item"]
```


## Indexing Child Documents in Solr


### Search API Processor Demo

1. Install this module.
1. Go to `/admin/config/search/search-api/index/my-index/processors` and enable the `WebVTT Extractor` processor.
1. Go to `/admin/config/search/search-api/index/my-index/fields/add/nojs`. Add the `Extracted WebVTT` field and save.

This method of indexing WebVTT files as child documents in Solr using a Search API processor can be used for WebVTT files that belong directly to the node, but does not work when they belong to a media belonging to the node.

The main challenge is finding a way to generate an array property field for the transcript file when it belongs to the node's media. 

**Possible ways to approach this:**

- In the Search API processor's `getPropertyDefinitions` function, add properties for the nested file fields. For reference, the `FileExtractor` processor class in Search API Attachments generates a custom field for each file field in the node. This may not be possible as we don't know how many levels of nesting there are.
- Index the file using computed fields (see [Dynamic/Virtual field values using computed field property classes](https://www.drupal.org/docs/drupal-apis/entity-api/dynamicvirtual-field-values-using-computed-field-property-classes)). This is how the Search API patch creates properties for fields within the media object. This method will likely require a custom Drupal FieldType plugin. The computed field should return an array so that it can be indexed as child documents. Might be able to follow a similar approach to this: [How to properly define and return simple array computed field property](https://drupal.stackexchange.com/questions/267759/how-to-properly-define-and-return-simple-array-computed-field-property).

**Notes:**
1. In `src/Plugin/search_api/data_type`, there is a custom Search API data type.
1. In `solr_vtt.module`, the custom Search API data type is mapped to a Drupal TypedData plugin using `hook_search_api_field_type_mapping_alter()`.
1. In `src/Plugin/search_api/processor/VttExtractor.php` is a Search API processor plugin that indexes a hardcoded array of WebVTT data.


### Curl Demo

See `solr-nested-docs-curl-demo.sh`.


### Example in Solr

```javascript
{
  id: audio1,
  ss_type: islandora_object,
  ss_title: "audio1",
  ss_author: "admin",
  _childDocuments_: [
    {
      id: transcript1-1,
      ss_type: transcript,
      its_cue_id: 1,
      ss_vtt_start: "00:00:12.488",
      ss_vtt_end: "00:00:23.847",
      ts_vtt_text: "apple"
    },
    {
      id: transcript1-2,
      ss_type: transcript,
      its_cue_id: 2,
      ss_vtt_start: "00:00:42.488",
      ss_vtt_end: "00:00:53.847",
      ts_vtt_text: "orange"
    }
  ]
}
```


### Block Join Query

To get parents with a matching child + the children that matched:
```
q={!parent which=<allParents>}<someChildren>
fl=*,[child parentFilter=<someParents> childFilter=<someChildren>]
```


### Additional Resources for Nested Documents

- [Nested Objects in Solr](https://yonik.com/solr-nested-objects/)

Solr 7:
- [Nested Child Documents](https://solr.apache.org/guide/7_1/uploading-data-with-index-handlers.html#nested-child-documents)
- [Block Join Parent Query Parser](https://solr.apache.org/guide/7_1/other-parsers.html#block-join-parent-query-parser)

Solr 8:
- [Indexing Nested Child Documents](https://solr.apache.org/guide/8_0/indexing-nested-documents.html#indexing-nested-documents)
- [Searching Nested Child Documents](https://solr.apache.org/guide/8_0/searching-nested-documents.html)

Solr 8+ provides a feature that lets you customize the name of child documents. Before version 8, the child documents are stored in the `_childDocuments_` field. For more info, see [Apache Solr - Error loading class solr.NestPathField for solr version 6.5](https://stackoverflow.com/questions/61127369/apache-solr-error-loading-class-solr-nestpathfield-for-solr-version-6-5).
