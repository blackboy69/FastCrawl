<?

include_once("name_parser.php");
include_once("scrape/db.inc");
db::init2("demandforce");

$p = new name_parser();

print_r($p->parse("Mr. Byron D. Whitlock"));
print_r($p->parse("Mr Byron D. Whitlock"));
print_r($p->parse("Mrs. Edna Rosele Tanya Hodge-Whitlock"));

print_r($p->parse("Ms Renee Whitlock"));


print_r($p->parse("Byron D. Whitlock"));
print_r($p->parse("Byron Whitlock"));
print_r($p->parse("Byron D. Whitlock II")); 
print_r($p->parse("Byron D. Whitlock Jr. Esq."));

print_r($p->parse("Byron D. Whitlock Jr. Esq. PHD"));

print_r($p->parse("Dr Donna Yock Jr PHD"));
