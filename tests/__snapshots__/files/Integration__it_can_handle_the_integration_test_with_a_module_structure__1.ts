import { LevelUpClass } from 'Level';

export type Enum = "yes" | "no";
export type IntegrationClass = {
string: string
nullable: string | null
default: string
int: number
boolean: boolean
float: number
object: object
array: Array<any>
mixed: any
none: unknown
var_annotated: string
union: number | string
annotated_array: Array<number>
complex_annotated_array: {
int: number
string: string
level_up: LevelUpClass
}
complex_union: number | string | Array<number | string>
enum: Enum
non_typescript_type: undefined
array_of_reference: Array<IntegrationItem>
replacement_type: string
annotated_replacement_type: string
array_annotated_replacement_type: Array<string>
level_up_class: LevelUpClass
readonly readonly: string
optional?: string
constructor_annotated_array: Array<number>
constructor_inline_annotated_array: Array<LevelUpClass>
};
export type IntegrationItem = {
name: string
};
