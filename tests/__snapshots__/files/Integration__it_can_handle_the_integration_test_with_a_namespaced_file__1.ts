declare namespace Spatie.TypeScriptTransformer.Tests.Fakes.Integration{
export type Enum = "yes" | "no";
export type IntegrationClass = {
string: string
nullable: string | null
default: string
int: number
boolean: boolean
float: number
object: object
array: []
mixed: any
none: unknown
var_annotated: string
union: number | string
annotated_array: Array<number>
complex_annotated_array: {
int: number
string: string
level_up: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass
}
complex_union: number | string | Array<number | string>
enum: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Enum
non_typescript_type: undefined
array_of_reference: Array<Spatie.TypeScriptTransformer.Tests.Fakes.Integration.IntegrationItem>
replacement_type: string
annotated_replacement_type: string
array_annotated_replacement_type: Array<string>
level_up_class: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass
readonly readonly: string
optional?: string
constructor_annotated_array: Array<number>
constructor_inline_annotated_array: Array<Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass>
};
export type IntegrationItem = {
name: string
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level{
export type LevelUpClass = {
name: string
};
}
