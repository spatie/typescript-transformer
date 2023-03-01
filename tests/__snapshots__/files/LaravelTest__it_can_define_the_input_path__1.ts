declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses {
export type IntBackedEnum = {
name: string;
value: number;
};
export type SpatieEnum = 'draft' | 'published' | 'archived';
export type StringBackedEnum = {
name: string;
value: string;
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses.Attributes {
export type WithAlreadyTransformedAttributeAttribute = {an_int:number;a_bool:boolean;};
export type WithTypeScriptAttribute = {
};
export type YoloClass = {
};
export type WithTypeScriptTransformerAttribute = {
an_int: number;
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses.Enum {
export type TypeScriptEnum = {
};
export type TypeScriptEnumWithCustomTransformer = fake;
export type EnumWithName = {
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration {
export type Dto = {
string: string;
nullbable: string | null;
default: string;
int: number;
boolean: boolean;
float: number;
object: object;
array: Array<any>;
none: any;
documented_string: string;
mixed: number | string;
number: number;
documented_array: Array<number>;
mixed_with_array: number | string | Array<number | string>;
array_with_null: Array<number | null>;
enum: Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.Enum;
non_typescripted_type: any;
other_dto: Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.OtherDto;
other_dto_array: Array<Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.OtherDto>;
dto_with_children: Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.DtoWithChildren;
another_namespace_dto: Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.LevelUp.YetAnotherDto;
nullable_string: string | number | null;
reflection_replaced_default_type: string;
docblock_replaced_default_type: string;
array_replaced_default_type: Array<string>;
array_as_object: { [key: string]: any };
};
export type DtoWithChildren = {
name: string;
other_dto: Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.OtherDto;
other_dto_array: Array<Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.OtherDto>;
};
export type Enum = {
};
export type OtherDto = {
name: string;
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses.Integration.LevelUp {
export type YetAnotherDto = {
name: string;
};
}
declare namespace Spatie.TypeScriptTransformer.Tests.FakeClasses.States {
export type State = 'child' | 'other_child';
}
