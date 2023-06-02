# This file contains curl requests demoing the use of nested documents in solr.

# Get all documents
curl http://localhost:8983/solr/ISLANDORA/select?q=*

# Delete all documents
curl http://localhost:8983/solr/ISLANDORA/update?commitWithin=3000 -d '{delete:{query:"*:*"}}'

# Index nodes with child documents
curl http://localhost:8983/solr/ISLANDORA/update?commitWithin=3000 -d '
[
  {
    id: audio1-from-curl,
    ss_type: islandora_object,
    ss_title: "audio1 from curl",
    ss_author: "curl",
    _childDocuments_: [
      {
        id: transcript1-1,
        ss_type: transcript,
        its_cue_id: 1,
        ss_vtt_start: "00:00:12.488",
        ss_vtt_end: "00:00:23.847",
        ts_X3b_en_vtt_text: "apple"
      },
      {
        id: transcript1-2,
        ss_type: transcript,
        its_cue_id: 2,
        ss_vtt_start: "00:00:42.488",
        ss_vtt_end: "00:00:53.847",
        ts_X3b_en_vtt_text: "orange"
      },
      {
        id: transcript1-3,
        ss_type: transcript,
        its_cue_id: 3,
        ss_vtt_start: "00:00:55.488",
        ss_vtt_end: "00:00:59.847",
        ts_X3b_en_vtt_text: "pear"
      },
      {
        id: transcript1-4,
        ss_type: transcript,
        its_cue_id: 3,
        ss_vtt_start: "00:22:55.488",
        ss_vtt_end: "00:22:59.847",
        ts_X3b_en_vtt_text: "mango"
      },
    ]
  },
  {
    id: audio2-from-curl,
    ss_type: islandora_object,
    ss_title: "audio2 from curl",
    ss_author: "curl",
    _childDocuments_: [
      {
        id: transcript2-1,
        its_cue_id: 1,
        ss_vtt_start: "00:00:12.488",
        ss_vtt_end: "00:00:23.847",
        ts_X3b_en_vtt_text: "apple tree"
      },
      {
        id: transcript2-2,
        its_cue_id: 2,
        ss_vtt_start: "00:00:42.488",
        ss_vtt_end: "00:00:53.847",
        ts_X3b_en_vtt_text: "maple"
      },
      {
        id: transcript2-3,
        its_cue_id: 3,
        ss_vtt_start: "00:00:55.488",
        ss_vtt_end: "00:00:59.847",
        ts_X3b_en_vtt_text: "oak"
      },
    ]
  }
]'

# Search for parents using children
curl http://localhost:8983/solr/ISLANDORA/select -d '
q={!parent which=ss_type:islandora_object}ts_X3b_en_vtt_text:"apple"&
fl=*,[child parentFilter=ss_type:islandora_object childFilter=ts_X3b_en_vtt_text:"apple"]'
