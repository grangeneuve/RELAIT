USE [iagRelait]
GO
/****** Object:  Table [dbo].[Données exploitations]    Script Date: 10.02.2022 08:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Données exploitations](
	[Propriétaire] [nvarchar](255) NOT NULL,
	[Lait_max_prim] [int] NULL,
	[Lait_max_mult] [int] NULL,
	[PathParadoxFile] [nvarchar](255) NULL,
	[const_kg_Primipare] [int] NULL,
	[const_joursLact_min_debut] [int] NULL,
	[const_joursLact_min_production] [int] NULL,
	[FactorVel1] [float] NULL,
	[FactorVel2] [float] NULL,
	[N° exploitation Holstein] [int] NULL,
	[N° exploitation SwissHerdBook] [int] NULL,
	[N° exploitation Brune] [int] NULL,
	[Projet Antibiotiques] [nvarchar](255) NULL,
	[const_kg_Multipare] [int] NULL,
 CONSTRAINT [PK_Données exploitations] PRIMARY KEY CLUSTERED 
(
	[Propriétaire] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Données lait]    Script Date: 10.02.2022 08:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Données lait](
	[Propriétaire] [nvarchar](255) NOT NULL,
	[N° national] [nvarchar](255) NULL,
	[Nom] [nvarchar](255) NULL,
	[N° Travail] [int] NULL,
	[Age] [nvarchar](255) NULL,
	[Age (j)] [int] NULL,
	[DVêl] [datetime] NULL,
	[Date CL] [datetime] NULL,
	[Num ctrl] [int] NULL,
	[Etat] [nvarchar](255) NULL,
	[Lait] [float] NULL,
	[Obj] [float] NULL,
	[Lait-Obj] [float] NULL,
	[%Lait/Obj] [float] NULL,
	[Lait -1] [float] NULL,
	[% var] [float] NULL,
	[NbChutes] [int] NULL,
	[TB] [float] NULL,
	[TP] [float] NULL,
	[Urée] [int] NULL,
	[Cell] [int] NULL,
	[Numlact] [int] NULL,
	[Nbjours lact] [int] NULL,
	[Lait std] [int] NULL,
	[Cumul lait] [float] NULL,
	[Potentiel] [int] NULL,
	[Prim_Multi] [nvarchar](255) NULL,
	[Cat_Cellules] [nvarchar](255) NULL,
	[TB_TP] [float] NULL,
	[Persistance] [float] NULL,
	[Cat_j_lact] [nvarchar](255) NULL,
	[Cat_metab] [nvarchar](255) NULL,
	[Observations] [nvarchar](255) NULL,
	[Conseils] [nvarchar](255) NULL,
	[Quartiers] [nvarchar](255) NULL,
	[Posologie] [nvarchar](255) NULL,
	[ID_DL] [int] IDENTITY(1,1) NOT NULL,
	[Acetone] [int] NULL,
	[DateImport] [datetime] NULL,
 CONSTRAINT [PK_Données lait] PRIMARY KEY CLUSTERED 
(
	[ID_DL] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Données santé]    Script Date: 10.02.2022 08:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Données santé](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Propriétaire] [nvarchar](255) NOT NULL,
	[N° exploitation] [int] NOT NULL,
	[N° national] [nvarchar](255) NULL,
	[N° diagnostic] [int] NULL,
	[Niveau 1 diagnostic] [int] NULL,
	[Niveau 2 diagnostic] [int] NULL,
	[Niveau 3 diagnostic] [nvarchar](255) NULL,
	[Niveau 3a diagnostic] [nvarchar](255) NULL,
	[Niveau 4 diagnostic] [nvarchar](255) NULL,
	[Date diagnostic] [datetime] NULL,
	[Date début traitement] [datetime] NULL,
	[Date fin traitement] [datetime] NULL,
	[Date livraison du lait] [datetime] NULL,
	[Nom médicament] [nvarchar](255) NULL,
	[Quantité et Unité] [nvarchar](255) NULL,
	[Remarques diagnostics] [nvarchar](255) NULL,
	[Remarques traitements] [nvarchar](255) NULL,
	[N° diagnostic - exploitation] [nvarchar](255) NULL,
	[Niveau 1 - 2 diagnostic] [nvarchar](255) NULL,
	[DateImport] [datetime] NULL,
	[Naissance] [int] NULL,
 CONSTRAINT [PK_Données santé] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Etat repro]    Script Date: 10.02.2022 08:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Etat repro](
	[Propriétaire] [nvarchar](255) NOT NULL,
	[N° national] [nvarchar](255) NULL,
	[Nom] [nvarchar](255) NULL,
	[N° Travail] [int] NULL,
	[Etat] [nvarchar](255) NULL,
	[Date vêl] [datetime] NULL,
	[Nb j/vêl] [int] NULL,
	[RGV] [int] NULL,
	[Date Taris] [datetime] NULL,
	[IVV] [int] NULL,
	[Date 1ère IA/SN] [datetime] NULL,
	[IV-1ère IA/SN] [int] NULL,
	[Age 1- IA/SN] [nvarchar](255) NULL,
	[Age 1 IA/SN (j)] [nvarchar](255) NULL,
	[Date dern IA/SN] [datetime] NULL,
	[N° IA/SN] [int] NULL,
	[IV-IA/SN fécond] [int] NULL,
	[Date DG] [nvarchar](255) NULL,
	[Rés DG] [nvarchar](255) NULL,
	[Nbj gest] [int] NULL,
	[Date MRE prév] [datetime] NULL,
	[Date tar prévu] [datetime] NULL,
	[Date vêl prévu] [datetime] NULL,
	[Nom taureau] [nvarchar](255) NULL,
	[ID_ER] [int] IDENTITY(1,1) NOT NULL,
	[DateImport] [datetime] NULL,
 CONSTRAINT [PK_Etat repro] PRIMARY KEY CLUSTERED 
(
	[ID_ER] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Reproduction - IA]    Script Date: 10.02.2022 08:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Reproduction - IA](
	[Propriétaire] [nvarchar](255) NOT NULL,
	[N° national] [nvarchar](255) NULL,
	[N° Travail] [int] NULL,
	[Nom] [nvarchar](255) NULL,
	[Date] [datetime] NULL,
	[N° ins] [int] NULL,
	[RGV] [int] NULL,
	[DVêl] [datetime] NULL,
	[IV-IA] [int] NULL,
	[Nom taureau] [nvarchar](255) NULL,
	[ID_RIA] [int] IDENTITY(1,1) NOT NULL,
	[DateImport] [datetime] NULL,
 CONSTRAINT [PK_Reproduction - IA] PRIMARY KEY CLUSTERED 
(
	[ID_RIA] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[T_AlimIndiv]    Script Date: 10.02.2022 08:57:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[T_AlimIndiv](
	[Propriétaire] [nvarchar](255) NULL,
	[DateRapport] [nvarchar](255) NULL,
	[N° national] [nvarchar](255) NULL,
	[N° Travail] [int] NULL,
	[Nom] [nvarchar](255) NULL,
	[DVêl] [datetime] NULL,
	[Numlact] [int] NULL,
	[Nbjours lact] [int] NULL,
	[Lait] [float] NULL,
	[Kg_Calc] [float] NULL,
	[CalcPlan] [nvarchar](255) NULL,
	[R_Compl_par_kg_1_AlacUserLabel] [nvarchar](255) NULL,
	[C1] [float] NULL,
	[R_Compl_par_kg_2_AlacUserLabel] [nvarchar](255) NULL,
	[C2] [float] NULL,
	[R_Compl_par_kg_3_AlacUserLabel] [nvarchar](255) NULL,
	[C3] [float] NULL,
	[R_Compl_par_kg_4_AlacUserLabel] [nvarchar](255) NULL,
	[C4] [nvarchar](255) NULL,
	[R_Compl_par_kg_5_AlacUserLabel] [nvarchar](255) NULL,
	[M1] [float] NULL,
	[R_Compl_par_kg_6_AlacUserLabel] [nvarchar](255) NULL,
	[M2] [nvarchar](255) NULL,
	[ClassifVaches] [nvarchar](255) NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Y01]    Script Date: 10.02.2022 08:57:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Y01](
	[TypeData] [nvarchar](3) NULL,
	[VersionData] [int] NULL,
	[ID_Expl] [int] NOT NULL,
	[BDTA_Expl] [int] NULL,
	[N° national] [nvarchar](14) NULL,
	[race] [nvarchar](3) NULL,
	[Nom] [nvarchar](12) NULL,
	[Naissance] [int] NULL,
	[ID_Pere] [nvarchar](14) NULL,
	[Race_Pere] [nvarchar](3) NULL,
	[ID_Mere] [nvarchar](14) NULL,
	[Race_Mere] [nvarchar](3) NULL,
	[Race_Principale] [nvarchar](3) NULL,
	[Pourcent_Race_Principale] [int] NULL,
	[2eme_Race] [nvarchar](3) NULL,
	[Pourcent_2eme_Race] [int] NULL,
	[3eme_Race] [nvarchar](3) NULL,
	[Pourcent_3eme_Race] [int] NULL,
	[Sexe] [int] NULL,
	[ID_Expl_Eleveur] [int] NULL,
	[BDTA_Eleveur] [int] NULL,
	[Entree] [int] NULL,
	[Depart] [int] NULL,
	[Motif_Depart] [int] NULL,
	[Nom_Animal_long] [nvarchar](56) NULL,
	[Zone_Montagne_du] [int] NULL,
	[Zone_Montagne_au] [int] NULL,
	[Zone_Montagne_Lieu] [int] NULL,
	[Contingent_Supl] [int] NULL,
	[Contingent_Supl_Canton] [nvarchar](2) NULL,
	[Couleur] [int] NULL,
	[ID_Etranger] [nvarchar](20) NULL,
	[Syndicat] [nvarchar](20) NULL,
	[Triple_A] [nvarchar](6) NULL,
	[No_collier] [nvarchar](4) NULL,
	[Origine_Data] [int] NULL,
	[ID_Y01] [int] IDENTITY(1,1) NOT NULL,
	[DateImport] [datetime] NULL,
 CONSTRAINT [PK_Y01] PRIMARY KEY CLUSTERED 
(
	[ID_Y01] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
