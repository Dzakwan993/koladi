--
-- PostgreSQL database dump
--

\restrict 0IsM8CBPaXcT7h5tYsJllONRbNOkK8EGuYmqljmhpCUqsd1lfvenU9WAKqyAQpb

-- Dumped from database version 16.10 (Homebrew)
-- Dumped by pg_dump version 18.0

-- Started on 2025-12-27 15:11:44 WIB

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 22346)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 4255 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 897 (class 1247 OID 22358)
-- Name: conversation_scope; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.conversation_scope AS ENUM (
    'workspace',
    'company'
);


ALTER TYPE public.conversation_scope OWNER TO postgres;

--
-- TOC entry 900 (class 1247 OID 22364)
-- Name: payment_method_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.payment_method_enum AS ENUM (
    'midtrans',
    'manual'
);


ALTER TYPE public.payment_method_enum OWNER TO postgres;

--
-- TOC entry 272 (class 1255 OID 22369)
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 216 (class 1259 OID 22370)
-- Name: addons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.addons (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    addon_name character varying(100) NOT NULL,
    price_per_user numeric(12,2) NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.addons OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 22377)
-- Name: announcement_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcement_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid,
    user_id uuid
);


ALTER TABLE public.announcement_recipients OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 22381)
-- Name: announcements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcements (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    due_date date,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    auto_due date,
    company_id uuid
);


ALTER TABLE public.announcements OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 22390)
-- Name: attachments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attachments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    attachable_type character varying(100),
    attachable_id uuid,
    file_url text NOT NULL,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_size bigint,
    file_type character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.attachments OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 22399)
-- Name: board_columns; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.board_columns (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    "position" integer,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.board_columns OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 22405)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 22410)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 22415)
-- Name: calendar_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_events (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    start_datetime timestamp without time zone,
    end_datetime timestamp without time zone,
    recurrence character varying(100),
    is_private boolean DEFAULT false,
    is_online_meeting boolean DEFAULT false,
    meeting_link text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    company_id uuid,
    location character varying(255)
);


ALTER TABLE public.calendar_events OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 22425)
-- Name: calendar_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    event_id uuid,
    user_id uuid,
    status character varying(50),
    attendance boolean DEFAULT false
);


ALTER TABLE public.calendar_participants OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 22430)
-- Name: checklists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.checklists (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    title character varying(255) NOT NULL,
    is_done boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    "position" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.checklists OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 22438)
-- Name: colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.colors (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rgb character varying(20) NOT NULL
);


ALTER TABLE public.colors OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 22442)
-- Name: comments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    parent_comment_id uuid,
    commentable_type character varying(100),
    commentable_id uuid,
    user_id uuid,
    content text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.comments OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 22450)
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    address text,
    phone character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    trial_start timestamp(0) without time zone,
    trial_end timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    CONSTRAINT companies_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text])))
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 22460)
-- Name: conversation_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversation_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    user_id uuid,
    joined_at timestamp without time zone DEFAULT now(),
    is_admin boolean DEFAULT false,
    last_read_at timestamp without time zone
);


ALTER TABLE public.conversation_participants OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 22466)
-- Name: conversations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    type character varying(50) DEFAULT 'group'::character varying,
    name character varying(255),
    created_by uuid,
    updated_at timestamp without time zone,
    last_message_id uuid,
    scope public.conversation_scope DEFAULT 'workspace'::public.conversation_scope NOT NULL,
    company_id uuid
);


ALTER TABLE public.conversations OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 22473)
-- Name: document_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.document_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    document_id uuid NOT NULL,
    user_id uuid NOT NULL,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.document_recipients OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 22478)
-- Name: feedbacks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.feedbacks (
    id bigint NOT NULL,
    name character varying(255),
    email character varying(255),
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.feedbacks OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 22485)
-- Name: feedbacks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.feedbacks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.feedbacks_id_seq OWNER TO postgres;

--
-- TOC entry 4256 (class 0 OID 0)
-- Dependencies: 233
-- Name: feedbacks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.feedbacks_id_seq OWNED BY public.feedbacks.id;


--
-- TOC entry 234 (class 1259 OID 22486)
-- Name: files; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.files (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    folder_id uuid,
    workspace_id uuid,
    file_url text NOT NULL,
    is_private boolean DEFAULT false,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_path character varying(255),
    file_size integer,
    file_type character varying(255),
    company_id uuid
);


ALTER TABLE public.files OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 22494)
-- Name: folders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.folders (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    is_private boolean DEFAULT false,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    parent_id uuid,
    company_id uuid
);


ALTER TABLE public.folders OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 22501)
-- Name: insight_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insight_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    insight_id uuid,
    user_id uuid
);


ALTER TABLE public.insight_recipients OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 22505)
-- Name: insights; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insights (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    description text,
    delivery_days character varying(50),
    delivery_time time without time zone,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.insights OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 22514)
-- Name: invitations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invitations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email_target character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    invited_by uuid,
    company_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    expired_at timestamp without time zone,
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.invitations OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 22523)
-- Name: labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.labels (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    color_id uuid,
    created_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL
);


ALTER TABLE public.labels OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 22529)
-- Name: leave_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leave_requests (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    leave_type character varying(100),
    start_date date,
    end_date date,
    reason text,
    status character varying(50) DEFAULT 'pending'::character varying,
    approved_by uuid,
    attachment_url text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.leave_requests OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 22538)
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    sender_id uuid,
    content text,
    message_type character varying(50),
    reply_to_message_id uuid,
    is_edited boolean DEFAULT false,
    edited_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    read_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 22547)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 22550)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 4257 (class 0 OID 0)
-- Dependencies: 243
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 244 (class 1259 OID 22551)
-- Name: mindmap_nodes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmap_nodes (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    mindmap_id uuid NOT NULL,
    parent_id uuid,
    title character varying(255) NOT NULL,
    description text,
    type character varying(50) DEFAULT 'default'::character varying,
    x_position numeric(10,2) DEFAULT 0,
    y_position numeric(10,2) DEFAULT 0,
    connection_side character varying(20) DEFAULT 'auto'::character varying,
    sort_order integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmap_nodes OWNER TO postgres;

--
-- TOC entry 4258 (class 0 OID 0)
-- Dependencies: 244
-- Name: TABLE mindmap_nodes; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmap_nodes IS 'Tabel untuk menyimpan node-node dalam mind map';


--
-- TOC entry 245 (class 1259 OID 22564)
-- Name: mindmaps; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmaps (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid NOT NULL,
    title character varying(255) DEFAULT 'Mind Map Utama'::character varying NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmaps OWNER TO postgres;

--
-- TOC entry 4259 (class 0 OID 0)
-- Dependencies: 245
-- Name: TABLE mindmaps; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmaps IS 'Tabel untuk menyimpan mind map dalam workspace';


--
-- TOC entry 246 (class 1259 OID 22573)
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    company_id uuid NOT NULL,
    workspace_id uuid,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    context character varying(255),
    notifiable_type character varying(255) NOT NULL,
    notifiable_id uuid NOT NULL,
    actor_id uuid,
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp(0) without time zone,
    action_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifications_type_check CHECK (((type)::text = ANY (ARRAY[('chat'::character varying)::text, ('task'::character varying)::text, ('announcement'::character varying)::text, ('schedule'::character varying)::text])))
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 22580)
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.otp_verifications (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    otp character varying(6) NOT NULL,
    type character varying(20) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    is_used boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT otp_verifications_type_check CHECK (((type)::text = ANY (ARRAY[('register'::character varying)::text, ('reset_password'::character varying)::text])))
);


ALTER TABLE public.otp_verifications OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 22585)
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.otp_verifications_id_seq OWNER TO postgres;

--
-- TOC entry 4260 (class 0 OID 0)
-- Dependencies: 248
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- TOC entry 249 (class 1259 OID 22586)
-- Name: plans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.plans (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    plan_name character varying(100) NOT NULL,
    price_monthly numeric(12,2) NOT NULL,
    base_user_limit integer NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plans OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 22593)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(100) NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 22597)
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 22602)
-- Name: subscription_invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscription_invoices (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    subscription_id uuid NOT NULL,
    external_id character varying(255),
    payment_url character varying(255),
    amount numeric(12,2) NOT NULL,
    billing_month character varying(20) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    paid_at timestamp(0) without time zone,
    payment_details text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    payment_method public.payment_method_enum DEFAULT 'midtrans'::public.payment_method_enum NOT NULL,
    proof_of_payment character varying(500),
    admin_notes text,
    verified_at timestamp without time zone,
    verified_by uuid,
    payer_name character varying(255),
    payer_bank character varying(100),
    payer_account_number character varying(50),
    CONSTRAINT subscription_invoices_status_check CHECK (((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('paid'::character varying)::text, ('failed'::character varying)::text, ('expired'::character varying)::text])))
);


ALTER TABLE public.subscription_invoices OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 22611)
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscriptions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid NOT NULL,
    plan_id uuid,
    addons_user_count integer DEFAULT 0 NOT NULL,
    total_user_limit integer DEFAULT 0 NOT NULL,
    start_date timestamp(0) without time zone,
    end_date timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT subscriptions_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text, ('pending'::character varying)::text])))
);


ALTER TABLE public.subscriptions OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 22619)
-- Name: task_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_assignments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    user_id uuid,
    assigned_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.task_assignments OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 22624)
-- Name: task_labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_labels (
    task_id uuid NOT NULL,
    label_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.task_labels OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 22627)
-- Name: tasks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tasks (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    status character varying(100),
    board_column_id uuid,
    priority character varying(50),
    is_secret boolean DEFAULT false,
    start_datetime timestamp without time zone,
    due_datetime timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    phase character varying(100),
    completed_at timestamp with time zone
);


ALTER TABLE public.tasks OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 22636)
-- Name: user_companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    company_id uuid,
    roles_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.user_companies OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 22642)
-- Name: user_workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    roles_id uuid,
    join_date timestamp without time zone DEFAULT now(),
    status_active boolean DEFAULT true,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_workspaces OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 22650)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    full_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password text NOT NULL,
    google_id character varying(255),
    status_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    avatar character varying(500),
    email_verified_at timestamp without time zone,
    onboarding_step character varying(255),
    has_seen_onboarding boolean DEFAULT false,
    onboarding_type character varying(255),
    system_role_id uuid
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 22660)
-- Name: workspace_performance_snapshots; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspace_performance_snapshots (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    workspace_id uuid NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    period_type character varying(10) DEFAULT 'week'::character varying NOT NULL,
    metrics jsonb NOT NULL,
    performance_score integer DEFAULT 0 NOT NULL,
    quality_score integer DEFAULT 0 NOT NULL,
    risk_score integer DEFAULT 0 NOT NULL,
    suggestions jsonb NOT NULL,
    created_at timestamp(0) without time zone DEFAULT now(),
    updated_at timestamp(0) without time zone DEFAULT now(),
    version character varying(10) DEFAULT '1.0'::character varying NOT NULL
);


ALTER TABLE public.workspace_performance_snapshots OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 22673)
-- Name: workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid,
    type character varying(100),
    name character varying(255) NOT NULL,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    description text
);


ALTER TABLE public.workspaces OWNER TO postgres;

--
-- TOC entry 3772 (class 2604 OID 22681)
-- Name: feedbacks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks ALTER COLUMN id SET DEFAULT nextval('public.feedbacks_id_seq'::regclass);


--
-- TOC entry 3802 (class 2604 OID 22682)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 3816 (class 2604 OID 22683)
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- TOC entry 4204 (class 0 OID 22370)
-- Dependencies: 216
-- Data for Name: addons; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.addons (id, addon_name, price_per_user, description, is_active, created_at, updated_at) FROM stdin;
ddc2bae8-bc02-4f65-ad82-615a31ad810f	Tambahan User	4000.00	Tambah 1 user ke paket yang kamu pilih	t	2025-12-19 18:47:20	2025-12-19 18:47:20
\.


--
-- TOC entry 4205 (class 0 OID 22377)
-- Dependencies: 217
-- Data for Name: announcement_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcement_recipients (id, announcement_id, user_id) FROM stdin;
a3d5e7cc-733d-4fe6-be5e-51431c8899ce	7b490527-13ea-4f47-8ceb-abfb9dbcff47	37e80fa7-8b99-49fe-8f94-162af6b33a67
\.


--
-- TOC entry 4206 (class 0 OID 22381)
-- Dependencies: 218
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, workspace_id, created_by, title, description, due_date, is_private, created_at, updated_at, auto_due, company_id) FROM stdin;
482f4e4e-797c-49b2-a770-4347bbe878b8	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	aa	<p>aa</p>	2025-12-12	f	2025-12-11 11:56:11	2025-12-11 11:56:11	2025-12-12	\N
7b490527-13ea-4f47-8ceb-abfb9dbcff47	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	aa	<p>aa</p>	2025-12-12	t	2025-12-11 12:18:48	2025-12-11 12:53:26	\N	\N
ac307704-cd42-4e3c-8df3-aa43ed3227a0	\N	fdad37f2-c107-4473-893e-0e729c881a4b	aa	<p>aass</p><figure class="image"><img src="http://127.0.0.1:8000/storage/uploads/images/1765434176_T8rArU3O.jpg"></figure>	2025-12-12	f	2025-12-11 12:56:48	2025-12-11 13:23:04	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c
7945f560-4ebf-4e73-ad5e-3e8cf202cc6b	\N	fdad37f2-c107-4473-893e-0e729c881a4b	212	<p>121</p>	2025-12-23	f	2025-12-22 10:05:53	2025-12-22 10:05:53	2025-12-23	24015291-37bb-4357-bee7-4f28ad7e7c8c
b51334d9-ed1a-4913-b6ce-352b1a901342	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	asd	<p>adas</p>	2025-12-23	f	2025-12-22 10:11:18	2025-12-22 10:11:18	2025-12-23	\N
d2b1b8cd-dd70-435a-a9ea-9d050730b461	\N	de990493-03e6-4097-947d-851240d1cc0b	d	<p>d</p>	2025-12-25	f	2025-12-24 11:31:32	2025-12-24 11:31:32	2025-12-25	94ccbe72-90b8-48e1-b334-f8277e1739d3
316f59fe-26e5-41be-8880-1ac5358cd5d7	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	jjl	<p>k</p><p>&nbsp;</p>	2025-12-25	f	2025-12-24 11:40:43	2025-12-24 11:40:43	2025-12-25	\N
\.


--
-- TOC entry 4207 (class 0 OID 22390)
-- Dependencies: 219
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachments (id, attachable_type, attachable_id, file_url, uploaded_by, uploaded_at, file_name, file_size, file_type, created_at, updated_at) FROM stdin;
f73ecf7b-6f13-4ce4-8375-8187bcaa85ba	App\\Models\\Task	830a7562-8889-452a-a46e-71652d4c4b93	attachments/1765355592_risi.jpg	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:33:13	risi.jpg	67442	\N	2025-12-10 15:33:13.504367	2025-12-10 15:33:13.504367
1b173b8f-1a44-4539-bd6b-bd598caf5953	App\\Models\\Pengumuman	ac307704-cd42-4e3c-8df3-aa43ed3227a0	uploads/images/1765433345_30adhvbJ.jpg	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-11 13:09:05	risi.jpg	67442	image/jpeg	2025-12-11 13:09:05.284011	2025-12-11 13:09:05.284011
1ced1b73-347e-4442-bcf5-2ecc2b7b187d	App\\Models\\Pengumuman	ac307704-cd42-4e3c-8df3-aa43ed3227a0	uploads/images/1765434176_T8rArU3O.jpg	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-11 13:22:56	risi.jpg	67442	image/jpeg	2025-12-11 13:22:56.928231	2025-12-11 13:22:56.928231
685af92f-86d8-4a82-a84b-ff130fa42953	App\\Models\\Pengumuman	b51334d9-ed1a-4913-b6ce-352b1a901342	uploads/images/1765431158_pUob6CEt.png	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-11 12:32:40	Communication.png	1215	image/png	2025-12-11 12:32:40.81599	2025-12-11 12:32:40.81599
ae1e5a2c-8331-42bf-98ec-df750d2d6f32	App\\Models\\Pengumuman	b51334d9-ed1a-4913-b6ce-352b1a901342	uploads/files/1765431166_ErX5y9ZF.xlsx	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-11 12:32:46	Daftar_Perusahaan_10-Dec-2025_145146.xlsx	6730	application/vnd.openxmlformats-officedocument.spreadsheetml.sheet	2025-12-11 12:32:46.097149	2025-12-11 12:32:46.097149
\.


--
-- TOC entry 4208 (class 0 OID 22399)
-- Dependencies: 220
-- Data for Name: board_columns; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.board_columns (id, workspace_id, name, "position", created_by, created_at, updated_at, deleted_at) FROM stdin;
facace9e-f86b-4cc0-bfee-9018a583deb6	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	To Do List	1	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:28:48	2025-12-10 15:28:48	\N
8f3bb9d7-fa98-4805-bae0-54c528c3ddeb	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	Dikerjakan	2	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:28:48	2025-12-10 15:28:48	\N
ca7f0193-bfd7-482e-a0bc-a32057cdf109	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	Selesai	3	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:28:48	2025-12-10 15:28:48	\N
5356fcdb-e1ba-4dbd-8e72-6cdea39c1132	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	Batal	4	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:28:48	2025-12-10 15:28:48	\N
8432a801-6c2d-4011-9531-f3c3a0915d74	82ebb561-7977-44b2-9f2d-6fb732187a30	To Do List	1	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:31:40	2025-12-10 15:31:40	\N
0f6c34bf-e2d1-4070-ab79-9a9d35fef8e0	82ebb561-7977-44b2-9f2d-6fb732187a30	Dikerjakan	2	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:31:40	2025-12-10 15:31:40	\N
6ce5b118-6643-4214-a625-db3999c3ba16	82ebb561-7977-44b2-9f2d-6fb732187a30	Selesai	3	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:31:40	2025-12-10 15:31:40	\N
ef8d6258-f287-4785-be46-f8dceaf07f2a	82ebb561-7977-44b2-9f2d-6fb732187a30	Batal	4	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:31:40	2025-12-10 15:31:40	\N
194fea5f-6e2f-45ed-8eaa-41a046c6baea	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	ss	5	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:35:39	2025-12-10 15:41:29	2025-12-10 15:41:29
cde0745f-f768-4989-b023-e08409f318ce	a8e2b296-6d9b-4563-b502-103b35c3e134	To Do List	1	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 11:21:09	2025-12-22 11:21:09	\N
7f48ee40-7faa-41b5-bf1f-119e84d75692	a8e2b296-6d9b-4563-b502-103b35c3e134	Dikerjakan	2	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 11:21:09	2025-12-22 11:21:09	\N
95b76a81-851f-4ec8-ba33-912f8520a2be	a8e2b296-6d9b-4563-b502-103b35c3e134	Selesai	3	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 11:21:09	2025-12-22 11:21:09	\N
ce19934b-6887-44fb-b670-d333972118fc	a8e2b296-6d9b-4563-b502-103b35c3e134	Batal	4	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 11:21:09	2025-12-22 11:21:09	\N
8fd146af-d923-4637-aba2-54690ba1827b	d08bf7f0-18e2-46dc-963b-b8be84b15673	To Do List	1	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:21:11	2025-12-27 09:21:11	\N
b4df237d-91d3-489e-a37c-dcf126f16728	d08bf7f0-18e2-46dc-963b-b8be84b15673	Dikerjakan	2	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:21:11	2025-12-27 09:21:11	\N
3b7c04ed-6fe2-4de8-ac49-e6a5e301d126	d08bf7f0-18e2-46dc-963b-b8be84b15673	Selesai	3	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:21:11	2025-12-27 09:21:11	\N
38f66149-b572-420f-8ce0-61de4b1a1364	d08bf7f0-18e2-46dc-963b-b8be84b15673	Batal	4	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:21:11	2025-12-27 09:21:11	\N
55ca8e78-6876-4028-a249-beff4b5c09a6	d08bf7f0-18e2-46dc-963b-b8be84b15673	Pending	5	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:24:39	2025-12-27 09:24:39	\N
\.


--
-- TOC entry 4209 (class 0 OID 22405)
-- Dependencies: 221
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-board_columns_a8e2b296-6d9b-4563-b502-103b35c3e134	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjQ6e2k6MDtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImNkZTA3NDVmLWY3NjgtNDk4OS1iMDIzLWUwODQwOWYzMThjZSI7czo0OiJuYW1lIjtzOjEwOiJUbyBEbyBMaXN0IjtzOjg6InBvc2l0aW9uIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJhOGUyYjI5Ni02ZDliLTQ1NjMtYjUwMi0xMDNiMzVjM2UxMzQiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJjZGUwNzQ1Zi1mNzY4LTQ5ODktYjAyMy1lMDg0MDlmMzE4Y2UiO3M6NDoibmFtZSI7czoxMDoiVG8gRG8gTGlzdCI7czo4OiJwb3NpdGlvbiI7aToxO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYThlMmIyOTYtNmQ5Yi00NTYzLWI1MDItMTAzYjM1YzNlMTM0Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MTtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjdmNDhlZTQwLTdmYWEtNDFiNS1iZjFmLTExOWU4NGQ3NTY5MiI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJhOGUyYjI5Ni02ZDliLTQ1NjMtYjUwMi0xMDNiMzVjM2UxMzQiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI3ZjQ4ZWU0MC03ZmFhLTQxYjUtYmYxZi0xMTllODRkNzU2OTIiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYThlMmIyOTYtNmQ5Yi00NTYzLWI1MDItMTAzYjM1YzNlMTM0Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6Ijk1Yjc2YTgxLTg1MWYtNGVjOC1iYTMzLTkxMmY4NTIwYTJiZSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE4ZTJiMjk2LTZkOWItNDU2My1iNTAyLTEwM2IzNWMzZTEzNCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6Ijk1Yjc2YTgxLTg1MWYtNGVjOC1iYTMzLTkxMmY4NTIwYTJiZSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE4ZTJiMjk2LTZkOWItNDU2My1iNTAyLTEwM2IzNWMzZTEzNCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO31pOjM7TzoyMjoiQXBwXE1vZGVsc1xCb2FyZENvbHVtbiI6MzU6e3M6MTM6IgAqAGNvbm5lY3Rpb24iO3M6NToicGdzcWwiO3M6ODoiACoAdGFibGUiO3M6MTM6ImJvYXJkX2NvbHVtbnMiO3M6MTM6IgAqAHByaW1hcnlLZXkiO3M6MjoiaWQiO3M6MTA6IgAqAGtleVR5cGUiO3M6Njoic3RyaW5nIjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MDtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo0OntzOjI6ImlkIjtzOjM2OiJjZTE5OTM0Yi02ODg3LTQ0ZmItYjY3MC1kMzMzOTcyMTE4ZmMiO3M6NDoibmFtZSI7czo1OiJCYXRhbCI7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYThlMmIyOTYtNmQ5Yi00NTYzLWI1MDItMTAzYjM1YzNlMTM0Ijt9czoxMToiACoAb3JpZ2luYWwiO2E6NDp7czoyOiJpZCI7czozNjoiY2UxOTkzNGItNjg4Ny00NGZiLWI2NzAtZDMzMzk3MjExOGZjIjtzOjQ6Im5hbWUiO3M6NToiQmF0YWwiO3M6ODoicG9zaXRpb24iO2k6NDtzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE4ZTJiMjk2LTZkOWItNDU2My1iNTAyLTEwM2IzNWMzZTEzNCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO319czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1766802344
laravel-cache-board_columns_d08bf7f0-18e2-46dc-963b-b8be84b15673	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImI0ZGYyMzdkLTkxZDMtNDg5ZS1hMzdjLWRjZjEyNmYxNjcyOCI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJkMDhiZjdmMC0xOGUyLTQ2ZGMtOTYzYi1iOGJlODRiMTU2NzMiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJiNGRmMjM3ZC05MWQzLTQ4OWUtYTM3Yy1kY2YxMjZmMTY3MjgiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiZDA4YmY3ZjAtMThlMi00NmRjLTk2M2ItYjhiZTg0YjE1NjczIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjNiN2MwNGVkLTZmZTItNGRlOC1hYzQ5LWU2YTVlMzAxZDEyNiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImQwOGJmN2YwLTE4ZTItNDZkYy05NjNiLWI4YmU4NGIxNTY3MyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjNiN2MwNGVkLTZmZTItNGRlOC1hYzQ5LWU2YTVlMzAxZDEyNiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImQwOGJmN2YwLTE4ZTItNDZkYy05NjNiLWI4YmU4NGIxNTY3MyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO31pOjQ7TzoyMjoiQXBwXE1vZGVsc1xCb2FyZENvbHVtbiI6MzU6e3M6MTM6IgAqAGNvbm5lY3Rpb24iO3M6NToicGdzcWwiO3M6ODoiACoAdGFibGUiO3M6MTM6ImJvYXJkX2NvbHVtbnMiO3M6MTM6IgAqAHByaW1hcnlLZXkiO3M6MjoiaWQiO3M6MTA6IgAqAGtleVR5cGUiO3M6Njoic3RyaW5nIjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MDtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo0OntzOjI6ImlkIjtzOjM2OiI1NWNhOGU3OC02ODc2LTQwMjgtYTI0OS1iZWZmNGI1YzA5YTYiO3M6NDoibmFtZSI7czo3OiJQZW5kaW5nIjtzOjg6InBvc2l0aW9uIjtpOjU7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJkMDhiZjdmMC0xOGUyLTQ2ZGMtOTYzYi1iOGJlODRiMTU2NzMiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI1NWNhOGU3OC02ODc2LTQwMjgtYTI0OS1iZWZmNGI1YzA5YTYiO3M6NDoibmFtZSI7czo3OiJQZW5kaW5nIjtzOjg6InBvc2l0aW9uIjtpOjU7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJkMDhiZjdmMC0xOGUyLTQ2ZGMtOTYzYi1iOGJlODRiMTU2NzMiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTo0OntzOjg6InBvc2l0aW9uIjtzOjc6ImludGVnZXIiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6ODoiZGF0ZXRpbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6ODoiZGF0ZXRpbWUiO3M6MTA6ImRlbGV0ZWRfYXQiO3M6ODoiZGF0ZXRpbWUiO31zOjE3OiIAKgBjbGFzc0Nhc3RDYWNoZSI7YTowOnt9czoyMToiACoAYXR0cmlidXRlQ2FzdENhY2hlIjthOjA6e31zOjEzOiIAKgBkYXRlRm9ybWF0IjtOO3M6MTA6IgAqAGFwcGVuZHMiO2E6MDp7fXM6MTk6IgAqAGRpc3BhdGNoZXNFdmVudHMiO2E6MDp7fXM6MTQ6IgAqAG9ic2VydmFibGVzIjthOjA6e31zOjEyOiIAKgByZWxhdGlvbnMiO2E6MDp7fXM6MTA6IgAqAHRvdWNoZXMiO2E6MDp7fXM6Mjc6IgAqAHJlbGF0aW9uQXV0b2xvYWRDYWxsYmFjayI7TjtzOjI2OiIAKgByZWxhdGlvbkF1dG9sb2FkQ29udGV4dCI7TjtzOjEwOiJ0aW1lc3RhbXBzIjtiOjE7czoxMzoidXNlc1VuaXF1ZUlkcyI7YjowO3M6OToiACoAaGlkZGVuIjthOjA6e31zOjEwOiIAKgB2aXNpYmxlIjthOjA6e31zOjExOiIAKgBmaWxsYWJsZSI7YTo1OntpOjA7czoyOiJpZCI7aToxO3M6MTI6IndvcmtzcGFjZV9pZCI7aToyO3M6NDoibmFtZSI7aTozO3M6ODoicG9zaXRpb24iO2k6NDtzOjEwOiJjcmVhdGVkX2J5Ijt9czoxMDoiACoAZ3VhcmRlZCI7YToxOntpOjA7czoxOiIqIjt9czo4OiIAKgBkYXRlcyI7YToxOntpOjA7czoxMDoiZGVsZXRlZF9hdCI7fXM6MTY6IgAqAGZvcmNlRGVsZXRpbmciO2I6MDt9fXM6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDt9	1766804628
\.


--
-- TOC entry 4210 (class 0 OID 22410)
-- Dependencies: 222
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 4211 (class 0 OID 22415)
-- Dependencies: 223
-- Data for Name: calendar_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_events (id, workspace_id, created_by, title, description, start_datetime, end_datetime, recurrence, is_private, is_online_meeting, meeting_link, created_at, updated_at, deleted_at, company_id, location) FROM stdin;
019b25ed-b36f-70dd-9307-6941b0f46347	\N	fdad37f2-c107-4473-893e-0e729c881a4b	rapat keuangan umum	<p>Rapat keuangan penting untuk masalah perusahaan dan gaji karyawan</p>	2025-12-16 13:50:00	2025-12-16 14:50:00	\N	f	t	https://us05web.zoom.us/j/88248750620?pwd=yZu3hxd38Y2xaJTNUckKtTxuNKYXQD.1	2025-12-16 13:51:33	2025-12-16 13:51:33	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b25ee-7f83-7165-852b-66eb2f6def72	\N	fdad37f2-c107-4473-893e-0e729c881a4b	ss	<p>ss</p>	2025-12-17 13:51:00	2025-12-19 14:51:00	\N	f	f	\N	2025-12-16 13:52:25	2025-12-16 13:52:25	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	Ruang Meeting Lantai 3
019b25ed-b0ee-70f2-8300-bed3a1c16ff0	\N	fdad37f2-c107-4473-893e-0e729c881a4b	rapat keuangan umum	<p>Rapat keuangan penting untuk masalah perusahaan dan gaji karyawan</p>	2025-12-16 13:50:00	2025-12-16 14:50:00	\N	f	t	https://us05web.zoom.us/j/88248750620?pwd=yZu3hxd38Y2xaJTNUckKtTxuNKYXQD.1	2025-12-16 13:51:32	2025-12-16 13:52:36	2025-12-16 13:52:36	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b3a40-92ae-70aa-a87b-a39815230794	\N	fdad37f2-c107-4473-893e-0e729c881a4b	22	\N	2025-12-20 12:34:00	2025-12-20 13:34:00	\N	f	t	https://claude.ai/chat/2dd9c4a6-64d9-4e65-86a2-f6bc4b7c7126	2025-12-20 12:34:29	2025-12-20 13:02:40	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b25f1-27ef-7195-b68a-a941bbdf836e	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	ss	\N	2025-12-21 19:54:00	2025-12-22 21:54:00	\N	t	t	https://us05web.zoom.us/j/88248750620?pwd=yZu3hxd38Y2xaJTNUckKtTxuNKYXQD.1	2025-12-16 13:55:20	2025-12-20 13:25:05	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b4403-912f-7062-a7d6-f48482e0f938	\N	fdad37f2-c107-4473-893e-0e729c881a4b	ppp	\N	2025-12-23 10:03:00	2025-12-24 11:03:00	\N	f	f	\N	2025-12-22 10:04:03	2025-12-22 10:07:17	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	jakarta
019b4407-7882-738d-88ba-c9f506a0765f	\N	fdad37f2-c107-4473-893e-0e729c881a4b	sss	<p>sss</p>	2025-12-27 10:08:00	2025-12-27 11:08:00	\N	f	f	\N	2025-12-22 10:08:18	2025-12-22 10:08:18	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	Ruang Meeting Lantai 3
019b4418-7ee9-712d-a3c7-566379a1a4a5	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	asa	<p>sss</p>	2025-12-23 16:26:00	2025-12-23 17:26:00	\N	f	f	\N	2025-12-22 10:26:54	2025-12-22 10:26:54	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	Ruang Meeting Lantai 3
019b442b-128e-73f4-b7f6-81e02c4965b1	\N	fdad37f2-c107-4473-893e-0e729c881a4b	vvv	<p>saa</p>	2025-12-22 18:46:00	2025-12-22 21:46:00	\N	f	f	\N	2025-12-22 10:47:12	2025-12-22 10:47:12	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	Ruang Meeting Lantai 3
019b442e-9415-7134-8e31-f7aa20df1532	\N	fdad37f2-c107-4473-893e-0e729c881a4b	aaa	<p>ss</p>	2025-12-25 10:50:00	2025-12-25 11:50:00	\N	f	t	https://us05web.zoom.us/j/88248750620?pwd=yZu3hxd38Y2xaJTNUckKtTxuNKYXQD.1	2025-12-22 10:51:01	2025-12-22 10:51:01	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b4430-c36c-7110-9b2d-2a1ffac1dbb1	\N	fdad37f2-c107-4473-893e-0e729c881a4b	444	<p>44</p>	2025-12-26 10:53:00	2025-12-26 11:53:00	\N	f	t	https://us05web.zoom.us/postattendee?mn=3n35ygOqjWp6oMGC1KFESvWONjUcUFguGvk8.zKbwz6ZduHzxtH_A	2025-12-22 10:53:25	2025-12-22 10:53:25	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N
019b444d-281e-70b7-b740-2e826fec47dd	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	asdfasdf	\N	2025-12-22 11:24:00	2025-12-22 12:24:00	\N	f	t	https://us05web.zoom.us/j/81913408501?pwd=byEaNGTnju3gtRgLc8X2Tg1d7s1Xny.1	2025-12-22 11:24:25	2025-12-22 11:24:25	\N	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N
019b444d-a56d-7208-8bb3-49a8b9230f19	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	rapat 2	\N	2025-12-24 11:24:00	2025-12-24 12:24:00	\N	f	t	https://us05web.zoom.us/j/81913408501?pwd=byEaNGTnju3gtRgLc8X2Tg1d7s1Xny.1	2025-12-22 11:24:57	2025-12-22 11:24:57	\N	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N
019b4464-a75e-70e1-8e03-f5a91e4a2b44	\N	de990493-03e6-4097-947d-851240d1cc0b	lkjkldafs	\N	2025-12-22 11:49:00	2025-12-22 12:49:00	\N	f	t	https://us05web.zoom.us/j/81913408501?pwd=byEaNGTnju3gtRgLc8X2Tg1d7s1Xny.1	2025-12-22 11:50:05	2025-12-22 11:50:05	\N	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N
019b4465-9bf3-7142-9013-02c0fa5ebeab	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	asdfsadf	\N	2025-12-23 11:50:00	2025-12-23 12:50:00	\N	f	t	https://us05web.zoom.us/j/81913408501?pwd=byEaNGTnju3gtRgLc8X2Tg1d7s1Xny.1	2025-12-22 11:51:08	2025-12-22 11:51:08	\N	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N
\.


--
-- TOC entry 4212 (class 0 OID 22425)
-- Dependencies: 224
-- Data for Name: calendar_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_participants (id, event_id, user_id, status, attendance) FROM stdin;
ebb2679c-b787-4b92-a5cb-2d2dde520790	019b25ed-b0ee-70f2-8300-bed3a1c16ff0	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
724f8637-ba00-4fcd-ac89-d3ee66a96a77	019b25ee-7f83-7165-852b-66eb2f6def72	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
02e28a1a-05af-4391-9c01-dd8e03aa0659	019b3a40-92ae-70aa-a87b-a39815230794	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
cb497109-8d40-4133-abf4-bfa886720814	019b3a40-92ae-70aa-a87b-a39815230794	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	t
d100ed13-d4bf-4c6e-9864-641afe2a27f5	019b25ed-b36f-70dd-9307-6941b0f46347	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	t
2d5e61d4-4794-4088-bb18-b51ab521df9e	019b25ed-b36f-70dd-9307-6941b0f46347	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	t
350566ac-5bb0-4d99-b7ba-91421bd55c68	019b25f1-27ef-7195-b68a-a941bbdf836e	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	t
e1d4eabe-503c-4602-8fc2-4643b970c047	019b4403-912f-7062-a7d6-f48482e0f938	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
cead71f0-5e06-44de-9edf-80d50d9d488e	019b4403-912f-7062-a7d6-f48482e0f938	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	f
bb569795-d097-4976-a01c-a76c36cbf577	019b4407-7882-738d-88ba-c9f506a0765f	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
d7647792-1022-47a3-9a02-0909ab565e20	019b4407-7882-738d-88ba-c9f506a0765f	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	f
c620701f-aaa8-4d1b-8945-e503953ee97b	019b4418-7ee9-712d-a3c7-566379a1a4a5	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	f
487b2569-3425-4969-9b58-2754ee81517a	019b442b-128e-73f4-b7f6-81e02c4965b1	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	f
68fbeb96-6d12-4cbf-9bdc-7ee3e1cf562c	019b442b-128e-73f4-b7f6-81e02c4965b1	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
e78bcebb-2fe9-476d-b0d4-5b9031f7c379	019b442e-9415-7134-8e31-f7aa20df1532	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
44998441-3569-4b3c-9ade-eabdf306fa3c	019b442e-9415-7134-8e31-f7aa20df1532	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	t
a94d5c78-ff4d-4970-8050-a9ee365683cd	019b4430-c36c-7110-9b2d-2a1ffac1dbb1	37e80fa7-8b99-49fe-8f94-162af6b33a67	accepted	f
bd3a7256-dcaf-4da2-abc2-6c0547a666b1	019b4430-c36c-7110-9b2d-2a1ffac1dbb1	fdad37f2-c107-4473-893e-0e729c881a4b	accepted	f
7e0f3fc9-d61f-4c23-b9d0-8cf8942d99ee	019b444d-a56d-7208-8bb3-49a8b9230f19	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	accepted	f
edd31dae-2587-4d1a-bbe1-cf2f9fb24319	019b4464-a75e-70e1-8e03-f5a91e4a2b44	de990493-03e6-4097-947d-851240d1cc0b	accepted	f
93c792d6-7663-4779-aec4-f24844aae207	019b4464-a75e-70e1-8e03-f5a91e4a2b44	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	accepted	f
fcee0269-a981-4eb1-b8fa-88a3647a8b28	019b4465-9bf3-7142-9013-02c0fa5ebeab	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	accepted	f
d4d1e378-02db-46b8-9051-cc3500f730bc	019b444d-281e-70b7-b740-2e826fec47dd	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	accepted	t
\.


--
-- TOC entry 4213 (class 0 OID 22430)
-- Dependencies: 225
-- Data for Name: checklists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.checklists (id, task_id, title, is_done, created_at, updated_at, "position") FROM stdin;
5f5dc280-d6f9-48f8-8285-df83be25c1c4	830a7562-8889-452a-a46e-71652d4c4b93	ssss	t	2025-12-10 15:33:54	2025-12-10 15:34:50	0
\.


--
-- TOC entry 4214 (class 0 OID 22438)
-- Dependencies: 226
-- Data for Name: colors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.colors (id, rgb) FROM stdin;
2a854d03-557a-4457-b395-136d0baafcf5	#795548
2cf4a4f4-06a0-47c7-876b-2cf788e16351	#FF9800
34baa825-01f9-4b26-8fc4-519dfca5af6b	#9E9E9E
3f9fedb9-d632-4a8f-860a-05e0781fb70c	#000000
4712cec0-289c-44bd-9592-7bdaa5dbe883	#00796B
4ce6f29c-fbf7-4ade-b6a3-4e49ac28dec3	#8BC34A
5c0a1142-25ef-4373-9164-6137718a5b5f	#FFFFFF
62f25f1b-506e-4f9e-a0f5-bac0bc6d543d	#FF5722
69bed3f4-1659-49c1-8bbe-820e135f781f	#FF4C4C
6c8f270d-fc3d-45fa-a35a-64be13a797e9	#303F9F
6f736fa7-7b45-4b05-a3d4-cdad4851c02b	#607D8B
75442d05-1b79-446f-ae39-d3698531caa9	#FFC107
780ac390-64ae-4e4c-b599-cd07c0bfe105	#0288D1
792985e0-1d2f-4830-b296-4c06a97a0c64	#7B1FA2
7f07267f-c7cb-430a-b840-4bf68072454b	#3F51B5
7f0e2934-5c76-41b6-b1bd-eb57c5509d53	#00BCD4
828fd126-71c8-4692-b990-a067faa78b2b	#512DA8
97176aa4-a936-42f4-9a67-b07fa555ad87	#C2185B
986e4c53-cf2a-4b8a-89ed-aa4fca0a00fb	#2E7D32
98d8ecc3-3b2c-4e2a-9f54-7138c43534e1	#CDDC39
9ca431e2-97f8-4eba-9656-0286baf96ee9	#4CAF50
a222164c-e36e-43ad-b9bb-6867f94c3f43	#009688
b2ed830a-1187-477c-813f-dbba8cf3114a	#FFEB3B
b7265dd1-078e-4365-a718-4a9736c43698	#E91E63
cee15dee-c5b0-477b-a9ea-d511e64f3b31	#FFCDD2
cfe9ecde-3169-4db8-b535-67ac6d728ff9	#03A9F4
d6d3bbb2-c943-433c-ac71-c451925134f7	#2196F3
f4f8cdec-f51b-439d-a4ca-38a232480fa3	#F44336
fa32a7da-4d6b-4149-ac51-588bb48f6555	#673AB7
fe4955e8-9aad-4677-b0d8-3665c5c84b53	#9C27B0
\.


--
-- TOC entry 4215 (class 0 OID 22442)
-- Dependencies: 227
-- Data for Name: comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comments (id, parent_comment_id, commentable_type, commentable_id, user_id, content, created_at, updated_at, deleted_at) FROM stdin;
0a51024a-7e4e-414f-8dcb-082ea6fd553c	\N	App\\Models\\Pengumuman	6ac61fe0-f219-4d95-9057-221fa6502529	fdad37f2-c107-4473-893e-0e729c881a4b	<p>sssa</p>	2025-12-10 15:42:27	2025-12-10 15:44:16	2025-12-10 15:44:16
29fc49fd-f037-43dd-8e71-21409007db72	\N	App\\Models\\File	4a0d0108-3f8d-49c7-8984-76b5721c21f2	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	<p>sss</p>	2025-12-22 12:10:06	2025-12-22 12:10:06	\N
1fb52391-a557-48e9-93fd-5950475e0704	\N	App\\Models\\File	df240edc-ffec-411b-84ab-3e57abef0d8b	de990493-03e6-4097-947d-851240d1cc0b	<p>f</p>	2025-12-22 14:28:56	2025-12-22 14:28:56	\N
424c4878-6b39-425c-baad-1165d47c1489	1fb52391-a557-48e9-93fd-5950475e0704	App\\Models\\File	df240edc-ffec-411b-84ab-3e57abef0d8b	de990493-03e6-4097-947d-851240d1cc0b	<p>s</p>	2025-12-22 14:29:00	2025-12-22 14:29:00	\N
96e802b5-c5f6-426f-bbf2-6a5c6b04821b	\N	App\\Models\\File	df240edc-ffec-411b-84ab-3e57abef0d8b	de990493-03e6-4097-947d-851240d1cc0b	<p>dasf</p>	2025-12-22 14:36:48	2025-12-22 14:36:48	\N
83e506de-780a-4994-92a0-5cd3b4ab90b9	\N	App\\Models\\File	df240edc-ffec-411b-84ab-3e57abef0d8b	de990493-03e6-4097-947d-851240d1cc0b	<p>asdf</p>	2025-12-22 14:36:51	2025-12-22 14:36:51	\N
e3164fed-48dd-4c7d-af14-e98afc68351f	\N	App\\Models\\File	df240edc-ffec-411b-84ab-3e57abef0d8b	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	<p>dsaf</p>	2025-12-22 14:37:03	2025-12-22 14:37:03	\N
909b32bd-9274-42f0-bd37-ae94566008eb	\N	App\\Models\\File	4a0d0108-3f8d-49c7-8984-76b5721c21f2	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	<p>dsf</p>	2025-12-22 14:37:09	2025-12-22 14:37:09	\N
4f45bd76-066d-4b6d-9a50-45c93056b9e3	\N	App\\Models\\File	4a0d0108-3f8d-49c7-8984-76b5721c21f2	de990493-03e6-4097-947d-851240d1cc0b	<p>df</p>	2025-12-22 14:37:16	2025-12-22 14:37:16	\N
3bfabd1e-f65b-4130-88b4-0defe6e369be	\N	App\\Models\\File	df78bc9d-a4bf-406b-96f6-f50ac004622d	de990493-03e6-4097-947d-851240d1cc0b	<p>afd</p><p>&nbsp;</p>	2025-12-22 14:40:08	2025-12-22 14:40:08	\N
06b5a875-cc8a-4765-9296-16e96e6bea75	\N	App\\Models\\File	909ce530-15b7-44fa-988b-c6cd70874736	de990493-03e6-4097-947d-851240d1cc0b	<p>asdf</p>	2025-12-25 18:57:47	2025-12-25 18:57:47	\N
9e72411e-e14f-4129-89dc-4ff0bac24cfc	\N	App\\Models\\File	909ce530-15b7-44fa-988b-c6cd70874736	de990493-03e6-4097-947d-851240d1cc0b	<p>dsaf</p>	2025-12-25 18:57:50	2025-12-25 18:57:50	\N
\.


--
-- TOC entry 4216 (class 0 OID 22450)
-- Dependencies: 228
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, email, address, phone, created_at, updated_at, deleted_at, trial_start, trial_end, status) FROM stdin;
24015291-37bb-4357-bee7-4f28ad7e7c8c	Batu Aji	\N	\N	\N	2025-12-10 15:28:28	2025-12-19 18:27:51	\N	2025-12-10 15:28:28	\N	active
94ccbe72-90b8-48e1-b334-f8277e1739d3	asdf	\N	\N	\N	2025-12-22 11:20:55	2025-12-22 11:20:55	\N	2025-12-22 11:20:55	2025-12-29 11:20:55	trial
\.


--
-- TOC entry 4217 (class 0 OID 22460)
-- Dependencies: 229
-- Data for Name: conversation_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversation_participants (id, conversation_id, user_id, joined_at, is_admin, last_read_at) FROM stdin;
53f188eb-e1b0-45ce-a79c-3a561cfe1a5f	d430c72d-b84c-4038-a508-ec5d16747455	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:44:29.231399	f	2025-12-10 15:44:31
f84efd46-9cc8-4490-b7a5-fdff703ba62d	0217cffe-0c18-44b3-a1e2-9138a99bfda7	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-22 09:25:21.758156	f	2025-12-22 10:01:10
636faadb-9eb1-4142-8737-388d433c7494	8d3dda30-0d66-433e-828a-61e4eccddc6d	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-22 10:08:54.222068	f	\N
ade31968-aa40-4476-b052-a1fec3b0f59a	0217cffe-0c18-44b3-a1e2-9138a99bfda7	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-16 14:07:13.432584	f	2025-12-22 10:08:57
986a0e1e-1e7b-4566-826b-8bc2f084c691	8d3dda30-0d66-433e-828a-61e4eccddc6d	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-22 10:08:54.222068	t	2025-12-22 10:08:58
953e0440-1a16-496b-a650-e19852dd2eb5	d430c72d-b84c-4038-a508-ec5d16747455	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-10 15:44:42.53599	f	2025-12-22 10:09:13
63c02be8-60eb-4bc5-9430-61f949244c66	a12c328b-3d84-4ebb-89c6-0c6161b0fc1e	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 10:50:17.384602	f	\N
326058f7-aaaf-4afa-be57-df0e86548e95	28623a5c-6b03-4663-8aa9-5af4e1dd8c57	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 14:05:42.938597	f	\N
dd53441a-ae4c-464a-a719-6b80dbb9ec3f	28623a5c-6b03-4663-8aa9-5af4e1dd8c57	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 14:05:42.938597	t	2025-12-23 14:05:43
0aa47710-8490-4b4f-8aa1-ee0b58d10754	fb56b877-9800-465b-8c2a-13e08bc45f96	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 17:48:01.79647	f	\N
f8c35eb5-5258-416f-9e3b-afa3983b5756	fb56b877-9800-465b-8c2a-13e08bc45f96	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 17:48:06.133676	f	\N
a09d8c3f-0324-49cb-a261-b04a069a2dc8	a12c328b-3d84-4ebb-89c6-0c6161b0fc1e	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 17:48:47.44005	f	\N
\.


--
-- TOC entry 4218 (class 0 OID 22466)
-- Dependencies: 230
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, workspace_id, created_at, type, name, created_by, updated_at, last_message_id, scope, company_id) FROM stdin;
d430c72d-b84c-4038-a508-ec5d16747455	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	2025-12-10 15:44:29	group	Koladi	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:44:29	\N	workspace	\N
0217cffe-0c18-44b3-a1e2-9138a99bfda7	\N	2025-12-16 14:07:13	group	Batu Aji	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-16 14:07:13	\N	company	24015291-37bb-4357-bee7-4f28ad7e7c8c
8d3dda30-0d66-433e-828a-61e4eccddc6d	\N	2025-12-22 10:08:54	private	\N	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-22 10:08:54	\N	company	24015291-37bb-4357-bee7-4f28ad7e7c8c
a12c328b-3d84-4ebb-89c6-0c6161b0fc1e	a8e2b296-6d9b-4563-b502-103b35c3e134	2025-12-23 10:50:17	group	workspace 1	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 10:50:17	\N	workspace	\N
28623a5c-6b03-4663-8aa9-5af4e1dd8c57	a8e2b296-6d9b-4563-b502-103b35c3e134	2025-12-23 14:05:42	private	\N	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 14:05:42	\N	workspace	\N
fb56b877-9800-465b-8c2a-13e08bc45f96	\N	2025-12-23 17:48:01	group	asdf	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 17:48:01	\N	company	94ccbe72-90b8-48e1-b334-f8277e1739d3
\.


--
-- TOC entry 4219 (class 0 OID 22473)
-- Dependencies: 231
-- Data for Name: document_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.document_recipients (id, document_id, user_id, status, created_at, updated_at) FROM stdin;
485f3f4d-d87f-4798-98cd-80225405a750	ad4e691d-ef3e-4bfd-88b2-138f6c32ef60	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	t	2025-12-22 17:52:58	2025-12-23 10:43:25
9985a82e-8831-4143-89df-97a1e498b530	ad4e691d-ef3e-4bfd-88b2-138f6c32ef60	cf643231-ae73-4a00-b1cb-ec58544cf590	t	2025-12-22 23:40:04	2025-12-23 10:43:25
1a282e42-406a-4533-acf4-3a2e5ad4a7a5	73c7eec6-e22d-421c-8943-9a2ecf834687	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	t	2025-12-22 18:01:32	2025-12-23 11:15:50
464f6299-edfe-44b5-8688-b075155a406a	73c7eec6-e22d-421c-8943-9a2ecf834687	cf643231-ae73-4a00-b1cb-ec58544cf590	t	2025-12-22 23:36:01	2025-12-23 11:15:50
c88ddcd2-3c20-45bb-8b40-70e214dee43a	cf90873d-f360-4b9e-ab3c-6725931b0f4e	cf643231-ae73-4a00-b1cb-ec58544cf590	t	2025-12-23 11:18:37	2025-12-23 11:18:37
f97c5b47-8ed1-4f87-88c8-1d4abb4bc524	a2b4c9bc-9c75-4b43-8045-55ddc34f7b93	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	f	2025-12-22 18:00:17	2025-12-22 18:00:24
3c51d843-36e9-4930-be84-1159e16deec3	f3b6210f-3724-4742-abc2-98e677edf7aa	cf643231-ae73-4a00-b1cb-ec58544cf590	f	2025-12-23 12:01:36	2025-12-23 12:01:44
11f36731-125c-4bf7-8cba-dfb428082be5	f3b6210f-3724-4742-abc2-98e677edf7aa	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	f	2025-12-23 12:01:40	2025-12-23 12:01:44
\.


--
-- TOC entry 4220 (class 0 OID 22478)
-- Dependencies: 232
-- Data for Name: feedbacks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.feedbacks (id, name, email, message, created_at, updated_at) FROM stdin;
1	Batu Aji	rendi@gmail.com	sss	2025-12-20 13:43:02	2025-12-20 13:43:02
\.


--
-- TOC entry 4222 (class 0 OID 22486)
-- Dependencies: 234
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.files (id, folder_id, workspace_id, file_url, is_private, uploaded_by, uploaded_at, file_name, file_path, file_size, file_type, company_id) FROM stdin;
ad4e691d-ef3e-4bfd-88b2-138f6c32ef60	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/WhatsApp Image 2025-12-21 at 15.04.20.jpeg	t	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 17:39:22	WhatsApp Image 2025-12-21 at 15.04.20.jpeg	files/WhatsApp Image 2025-12-21 at 15.04.20.jpeg	118750	jpeg	\N
73c7eec6-e22d-421c-8943-9a2ecf834687	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/falyer koladi.png	t	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 17:45:26	falyer koladi.png	files/falyer koladi.png	2111856	png	\N
f512d0b2-968c-46d3-9270-e7512525581d	a2b4c9bc-9c75-4b43-8045-55ddc34f7b93	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/koladi_db (8).sql	f	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 11:12:32	koladi_db (8).sql	files/koladi_db (8).sql	120580	sql	\N
246907c9-083e-455b-a6ea-5ed82cfe3c5c	\N	\N	http://127.0.0.1:8000/storage/files/koladi_db (8).sql	f	cf643231-ae73-4a00-b1cb-ec58544cf590	2025-12-23 11:16:28	koladi_db (8).sql	files/koladi_db (8).sql	120580	sql	94ccbe72-90b8-48e1-b334-f8277e1739d3
909ce530-15b7-44fa-988b-c6cd70874736	\N	\N	http://127.0.0.1:8000/storage/files/koladi_db (8)(1).sql	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:17:10	koladi_db (8)(1).sql	files/koladi_db (8)(1).sql	120580	sql	94ccbe72-90b8-48e1-b334-f8277e1739d3
732cf85e-cc60-42d8-84d0-bde93dde10f2	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/koladi_db (8) (1).sql	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:49:26	koladi_db (8) (1).sql	files/koladi_db (8) (1).sql	120580	sql	\N
85435f69-c1b9-446a-8078-093dd09ce37d	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/koladi_db (8) (1)(1).sql	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:49:37	koladi_db (8) (1)(1).sql	files/koladi_db (8) (1)(1).sql	120580	sql	\N
9b57e782-d6e4-495d-a50a-d6eba20406fa	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/Branding Visual.pdf	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:49:46	Branding Visual.pdf	files/Branding Visual.pdf	757438	pdf	\N
04113355-3e08-405c-a1c8-ca76b97402df	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/1207.mp4	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:50:12	1207.mp4	files/1207.mp4	34833971	mp4	\N
16802bc9-5f11-4c36-be1c-6f416e6c61c6	\N	a8e2b296-6d9b-4563-b502-103b35c3e134	http://127.0.0.1:8000/storage/files/1207(1).mp4	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:50:22	1207(1).mp4	files/1207(1).mp4	34833971	mp4	\N
\.


--
-- TOC entry 4223 (class 0 OID 22494)
-- Dependencies: 235
-- Data for Name: folders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.folders (id, workspace_id, name, is_private, created_by, created_at, updated_at, deleted_at, parent_id, company_id) FROM stdin;
f58e173b-dc69-468a-835d-2149c11c3400	\N	Batu Aji	f	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-12 17:21:05	2025-12-12 17:21:05	\N	\N	24015291-37bb-4357-bee7-4f28ad7e7c8c
a2b4c9bc-9c75-4b43-8045-55ddc34f7b93	a8e2b296-6d9b-4563-b502-103b35c3e134	,	t	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 17:45:07	2025-12-22 17:45:12	\N	\N	\N
f3b6210f-3724-4742-abc2-98e677edf7aa	a8e2b296-6d9b-4563-b502-103b35c3e134	asdf	t	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 17:45:42	2025-12-22 17:45:47	\N	\N	\N
0c8d6e52-6c77-4c95-8f63-ec6ef6c7aaaa	\N	j	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:17:19	2025-12-23 11:17:19	\N	\N	94ccbe72-90b8-48e1-b334-f8277e1739d3
cf90873d-f360-4b9e-ab3c-6725931b0f4e	a8e2b296-6d9b-4563-b502-103b35c3e134	as	t	de990493-03e6-4097-947d-851240d1cc0b	2025-12-23 11:17:53	2025-12-23 11:18:37	\N	\N	\N
af8afadf-1b1c-48b4-a3f3-67795950ee40	a8e2b296-6d9b-4563-b502-103b35c3e134	kjh	f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 11:49:32	2025-12-23 11:49:32	\N	\N	\N
\.


--
-- TOC entry 4224 (class 0 OID 22501)
-- Dependencies: 236
-- Data for Name: insight_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insight_recipients (id, insight_id, user_id) FROM stdin;
\.


--
-- TOC entry 4225 (class 0 OID 22505)
-- Dependencies: 237
-- Data for Name: insights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insights (id, workspace_id, created_by, description, delivery_days, delivery_time, is_private, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 4226 (class 0 OID 22514)
-- Dependencies: 238
-- Data for Name: invitations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invitations (id, email_target, token, status, invited_by, company_id, created_at, expired_at, updated_at) FROM stdin;
d96ae789-c55e-49d3-9dc1-182df49453fb	rendyfoto10@gmail.com	cbUxnDpsXSQIA7GNqiCj02DR1Ej0HSgfGUP4sRbjavFLSgasGBWfzVdehAgRkXZb	accepted	fdad37f2-c107-4473-893e-0e729c881a4b	24015291-37bb-4357-bee7-4f28ad7e7c8c	2025-12-10 15:29:20	2025-12-13 15:29:20	2025-12-10 15:30:41.549157
3af40228-0a99-460d-be5a-a2c2a4ce67aa	ardhaniishere@gmail.com	AZbg2Xx0WXbpJhLp3mR9diGmq30goChM1GqaLoO3JP63tQFFQRy3DmpIQit7DpIj	accepted	de990493-03e6-4097-947d-851240d1cc0b	94ccbe72-90b8-48e1-b334-f8277e1739d3	2025-12-22 11:21:52	2025-12-25 11:21:52	2025-12-22 11:23:20.265749
1540696e-37b5-42d5-88b5-da44dfd5d0e0	naufal201080@gmail.com	S0yL02DYCNcidp1hSQBfpImdUfIe4FGMqNzFRbkYXHa5tXo7vkPgzOCsdKATgdjD	accepted	de990493-03e6-4097-947d-851240d1cc0b	94ccbe72-90b8-48e1-b334-f8277e1739d3	2025-12-22 15:32:53	2025-12-25 15:32:53	2025-12-22 15:33:28.87801
\.


--
-- TOC entry 4227 (class 0 OID 22523)
-- Dependencies: 239
-- Data for Name: labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.labels (id, name, color_id, created_at, updated_at) FROM stdin;
86644765-e407-4738-9fa8-1bdf1ccf1fb2	ddddd	2cf4a4f4-06a0-47c7-876b-2cf788e16351	2025-12-10 15:33:23	2025-12-10 15:33:23
\.


--
-- TOC entry 4228 (class 0 OID 22529)
-- Dependencies: 240
-- Data for Name: leave_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leave_requests (id, user_id, workspace_id, leave_type, start_date, end_date, reason, status, approved_by, attachment_url, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 4229 (class 0 OID 22538)
-- Dependencies: 241
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, sender_id, content, message_type, reply_to_message_id, is_edited, edited_at, deleted_at, created_at, is_read, read_at, updated_at) FROM stdin;
90cf457c-4ee6-41be-a7b7-dbf6d26ee40c	d430c72d-b84c-4038-a508-ec5d16747455	fdad37f2-c107-4473-893e-0e729c881a4b	ss	text	\N	f	\N	\N	2025-12-10 15:44:33	t	2025-12-10 15:44:44	2025-12-10 15:44:44
ae0cf0a5-4fdf-4e26-a4d1-0739928c09f5	0217cffe-0c18-44b3-a1e2-9138a99bfda7	37e80fa7-8b99-49fe-8f94-162af6b33a67	halo	text	\N	f	\N	\N	2025-12-22 10:00:46	t	2025-12-22 10:01:10	2025-12-22 10:01:10
2d5b7434-3023-4234-b598-efe6f5a6c234	0217cffe-0c18-44b3-a1e2-9138a99bfda7	fdad37f2-c107-4473-893e-0e729c881a4b	p valo	text	\N	f	\N	\N	2025-12-22 10:01:18	t	2025-12-22 10:01:19	2025-12-22 10:01:19
d000e6a0-8ead-4bc9-8c89-77efcd192875	28623a5c-6b03-4663-8aa9-5af4e1dd8c57	de990493-03e6-4097-947d-851240d1cc0b	woi	text	\N	f	\N	\N	2025-12-23 14:05:44	f	\N	2025-12-23 14:05:44
\.


--
-- TOC entry 4230 (class 0 OID 22547)
-- Dependencies: 242
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
3	2025_11_27_204411_create_subscription_tables	1
4	2025_11_30_205745_add_system_role_id_to_users_table	2
5	2025_12_20_180641_create_notifications_table	3
\.


--
-- TOC entry 4232 (class 0 OID 22551)
-- Dependencies: 244
-- Data for Name: mindmap_nodes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmap_nodes (id, mindmap_id, parent_id, title, description, type, x_position, y_position, connection_side, sort_order, created_at, updated_at) FROM stdin;
c7a55e03-db07-44dd-b443-bf774bd91097	9da6c12e-59d5-4eb7-b721-3b54837f0127	fccaa665-37e2-4883-b68a-e277312a6c74	s	s	default	240.00	240.00	auto	0	2025-12-10 15:45:40	2025-12-22 09:30:16
45cf7d7b-b0e5-411c-973b-7e6cedf29460	9da6c12e-59d5-4eb7-b721-3b54837f0127	c7a55e03-db07-44dd-b443-bf774bd91097	ff	d	default	580.00	80.00	auto	1	2025-12-16 14:16:46	2025-12-22 09:30:34
fccaa665-37e2-4883-b68a-e277312a6c74	9da6c12e-59d5-4eb7-b721-3b54837f0127	\N	New Node		default	640.00	480.00	auto	2	2025-12-22 09:14:04	2025-12-22 09:30:40
7528a3f6-c8b6-4153-87ba-45163864f09a	9da6c12e-59d5-4eb7-b721-3b54837f0127	c7a55e03-db07-44dd-b443-bf774bd91097	New Node		default	660.00	240.00	auto	3	2025-12-22 09:30:51	2025-12-22 09:30:51
bc437eb9-22b3-45ef-833e-bf9200973ed5	9da6c12e-59d5-4eb7-b721-3b54837f0127	\N	Idea		idea	460.00	280.00	auto	4	2025-12-22 09:30:51	2025-12-22 09:30:51
7348b1e1-f783-408c-a59d-c659fa5d4cd4	9da6c12e-59d5-4eb7-b721-3b54837f0127	\N	Topic		default	460.00	280.00	auto	5	2025-12-22 09:30:51	2025-12-22 09:30:51
\.


--
-- TOC entry 4233 (class 0 OID 22564)
-- Dependencies: 245
-- Data for Name: mindmaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmaps (id, workspace_id, title, description, created_at, updated_at) FROM stdin;
9da6c12e-59d5-4eb7-b721-3b54837f0127	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	Mind Map Koladi	Mind map untuk workspace Koladi	2025-12-10 15:44:50	2025-12-10 15:44:50
27e2d5c1-bafd-4c94-b7ff-3ccd45d505eb	a8e2b296-6d9b-4563-b502-103b35c3e134	Mind Map workspace 1	Mind map untuk workspace workspace 1	2025-12-23 14:03:29	2025-12-23 14:03:29
\.


--
-- TOC entry 4234 (class 0 OID 22573)
-- Dependencies: 246
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, company_id, workspace_id, type, title, message, context, notifiable_type, notifiable_id, actor_id, is_read, read_at, action_url, created_at, updated_at) FROM stdin;
019b4400-9135-70b8-b9bb-1a99462a1d56	fdad37f2-c107-4473-893e-0e729c881a4b	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	chat	Pesan baru dari Rivaldi	halo	Chat Perusahaan	App\\Models\\Message	ae0cf0a5-4fdf-4e26-a4d1-0739928c09f5	37e80fa7-8b99-49fe-8f94-162af6b33a67	f	\N	http://127.0.0.1:8000/company/24015291-37bb-4357-bee7-4f28ad7e7c8c/chat	2025-12-22 10:00:46	2025-12-22 10:00:46
019b4401-0dbd-72d9-9a6f-0febab8613f3	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	chat	Pesan baru dari Kuliah	p valo	Chat Perusahaan	App\\Models\\Message	2d5b7434-3023-4234-b598-efe6f5a6c234	fdad37f2-c107-4473-893e-0e729c881a4b	t	2025-12-22 10:01:31	http://127.0.0.1:8000/company/24015291-37bb-4357-bee7-4f28ad7e7c8c/chat	2025-12-22 10:01:18	2025-12-22 10:01:31
019b4402-3992-72c6-97b1-bf421a732972	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	task	Tugas baru ditugaskan	aadalah	12 · Koladi	App\\Models\\Task	abb225ff-4d9b-4440-a6d8-355f393a809a	fdad37f2-c107-4473-893e-0e729c881a4b	t	2025-12-22 10:02:56	http://127.0.0.1:8000/kanban-tugas/c6394c7b-a46d-44d9-aabc-348f2b8e69c0?task=abb225ff-4d9b-4440-a6d8-355f393a809a	2025-12-22 10:02:35	2025-12-22 10:02:56
019b4405-3f87-7152-85a4-e58371629dcb	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	announcement	Pengumuman baru	212	Pengumuman Perusahaan	App\\Models\\Pengumuman	7945f560-4ebf-4e73-ad5e-3e8cf202cc6b	fdad37f2-c107-4473-893e-0e729c881a4b	t	2025-12-22 10:06:05	http://127.0.0.1:8000/companies/24015291-37bb-4357-bee7-4f28ad7e7c8c/pengumuman-perusahaan/7945f560-4ebf-4e73-ad5e-3e8cf202cc6b	2025-12-22 10:05:53	2025-12-22 10:06:05
019b4407-789a-7107-9f75-d66c3fbc9465	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	schedule	Jadwal meeting baru	sss	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019b4407-7882-738d-88ba-c9f506a0765f	fdad37f2-c107-4473-893e-0e729c881a4b	t	2025-12-22 10:08:29	http://127.0.0.1:8000/jadwal-umum/019b4407-7882-738d-88ba-c9f506a0765f?company_id=24015291-37bb-4357-bee7-4f28ad7e7c8c	2025-12-22 10:08:18	2025-12-22 10:08:29
019b4418-7f02-72dc-bb54-faaa26c336fb	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	schedule	Jadwal meeting baru	asa	Workspace: Koladi	App\\Models\\CalendarEvent	019b4418-7ee9-712d-a3c7-566379a1a4a5	fdad37f2-c107-4473-893e-0e729c881a4b	t	2025-12-22 10:27:06	http://127.0.0.1:8000/workspace/c6394c7b-a46d-44d9-aabc-348f2b8e69c0/jadwal/019b4418-7ee9-712d-a3c7-566379a1a4a5	2025-12-22 10:26:54	2025-12-22 10:27:06
019b442b-12a3-728b-b91a-7e1070e0aa05	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	schedule	Jadwal meeting baru	vvv	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019b442b-128e-73f4-b7f6-81e02c4965b1	fdad37f2-c107-4473-893e-0e729c881a4b	f	\N	http://127.0.0.1:8000/jadwal-umum/019b442b-128e-73f4-b7f6-81e02c4965b1?company_id=24015291-37bb-4357-bee7-4f28ad7e7c8c	2025-12-22 10:47:12	2025-12-22 10:47:12
019b442e-9429-71fa-81ab-f5d0e3494ffb	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	schedule	Jadwal meeting baru	aaa	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019b442e-9415-7134-8e31-f7aa20df1532	fdad37f2-c107-4473-893e-0e729c881a4b	f	\N	http://127.0.0.1:8000/jadwal-umum/019b442e-9415-7134-8e31-f7aa20df1532?company_id=24015291-37bb-4357-bee7-4f28ad7e7c8c	2025-12-22 10:51:01	2025-12-22 10:51:01
019b4430-c382-71cf-a336-d823d4da8f37	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	\N	schedule	Jadwal meeting baru	444	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019b4430-c36c-7110-9b2d-2a1ffac1dbb1	fdad37f2-c107-4473-893e-0e729c881a4b	f	\N	http://127.0.0.1:8000/jadwal-umum/019b4430-c36c-7110-9b2d-2a1ffac1dbb1?company_id=24015291-37bb-4357-bee7-4f28ad7e7c8c	2025-12-22 10:53:25	2025-12-22 10:53:25
019b444d-282f-72f2-acf6-bda2c2c18ab3	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	schedule	Jadwal meeting baru	asdfasdf	Workspace: workspace 1	App\\Models\\CalendarEvent	019b444d-281e-70b7-b740-2e826fec47dd	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/workspace/a8e2b296-6d9b-4563-b502-103b35c3e134/jadwal/019b444d-281e-70b7-b740-2e826fec47dd	2025-12-22 11:24:25	2025-12-22 11:24:25
019b444d-a579-7154-bdd5-1d964d6020b7	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	schedule	Jadwal meeting baru	rapat 2	Workspace: workspace 1	App\\Models\\CalendarEvent	019b444d-a56d-7208-8bb3-49a8b9230f19	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/workspace/a8e2b296-6d9b-4563-b502-103b35c3e134/jadwal/019b444d-a56d-7208-8bb3-49a8b9230f19	2025-12-22 11:24:57	2025-12-22 11:24:57
019b4460-46a1-70a3-91c4-0be45f5115bc	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	task	Tugas baru ditugaskan	adf	adfs · workspace 1	App\\Models\\Task	b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/kanban-tugas/a8e2b296-6d9b-4563-b502-103b35c3e134?task=b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	2025-12-22 11:45:18	2025-12-22 11:45:18
019b4464-a76d-73f9-a0dd-d29f0158985e	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N	schedule	Jadwal meeting baru	lkjkldafs	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019b4464-a75e-70e1-8e03-f5a91e4a2b44	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/jadwal-umum/019b4464-a75e-70e1-8e03-f5a91e4a2b44?company_id=94ccbe72-90b8-48e1-b334-f8277e1739d3	2025-12-22 11:50:05	2025-12-22 11:50:05
019b4465-9bff-7054-b05a-a4db51590030	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	schedule	Jadwal meeting baru	asdfsadf	Workspace: workspace 1	App\\Models\\CalendarEvent	019b4465-9bf3-7142-9013-02c0fa5ebeab	de990493-03e6-4097-947d-851240d1cc0b	t	2025-12-22 11:51:23	http://127.0.0.1:8000/workspace/a8e2b296-6d9b-4563-b502-103b35c3e134/jadwal/019b4465-9bf3-7142-9013-02c0fa5ebeab	2025-12-22 11:51:08	2025-12-22 11:51:23
019b4a07-3473-71a8-8332-1b77a5dffe91	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	chat	Pesan baru dari kocak	woi	Chat Pribadi · workspace 1	App\\Models\\Message	d000e6a0-8ead-4bc9-8c89-77efcd192875	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/workspace/a8e2b296-6d9b-4563-b502-103b35c3e134/chat	2025-12-23 14:05:44	2025-12-23 14:05:44
019b4e75-2250-720c-bf86-433a0912e6bc	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	a8e2b296-6d9b-4563-b502-103b35c3e134	task	Tugas diperbarui	adf telah diperbarui	adfs · workspace 1	App\\Models\\Task	b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/kanban-tugas/a8e2b296-6d9b-4563-b502-103b35c3e134?task=b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	2025-12-24 10:44:18	2025-12-24 10:44:18
019b4ea0-63ac-7383-9eda-e759a69fb904	cf643231-ae73-4a00-b1cb-ec58544cf590	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N	announcement	Pengumuman baru	d	Pengumuman Perusahaan	App\\Models\\Pengumuman	d2b1b8cd-dd70-435a-a9ea-9d050730b461	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/companies/94ccbe72-90b8-48e1-b334-f8277e1739d3/pengumuman-perusahaan/d2b1b8cd-dd70-435a-a9ea-9d050730b461	2025-12-24 11:31:32	2025-12-24 11:31:32
019b4ea0-654c-7306-af5f-1a7dad138f6f	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N	announcement	Pengumuman baru	d	Pengumuman Perusahaan	App\\Models\\Pengumuman	d2b1b8cd-dd70-435a-a9ea-9d050730b461	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/companies/94ccbe72-90b8-48e1-b334-f8277e1739d3/pengumuman-perusahaan/d2b1b8cd-dd70-435a-a9ea-9d050730b461	2025-12-24 11:31:33	2025-12-24 11:31:33
019b5d9d-b44e-72fc-b1a1-62777ef209ad	cf643231-ae73-4a00-b1cb-ec58544cf590	94ccbe72-90b8-48e1-b334-f8277e1739d3	d08bf7f0-18e2-46dc-963b-b8be84b15673	task	Tugas baru ditugaskan	Review Berkas Transaksi	Belum ada fase · Keuangan	App\\Models\\Task	cc8807e5-adea-4591-b3e3-96fbdeb1e70e	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/kanban-tugas/d08bf7f0-18e2-46dc-963b-b8be84b15673?task=cc8807e5-adea-4591-b3e3-96fbdeb1e70e	2025-12-27 09:22:55	2025-12-27 09:22:55
019b5d9e-aab0-7289-8bcc-505b40e9afbb	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	d08bf7f0-18e2-46dc-963b-b8be84b15673	task	Tugas baru ditugaskan	Buat Laporan kas Desember	Finalisasi · Keuangan	App\\Models\\Task	6a69f730-4fd8-4ce1-bbeb-2ff8f5531703	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/kanban-tugas/d08bf7f0-18e2-46dc-963b-b8be84b15673?task=6a69f730-4fd8-4ce1-bbeb-2ff8f5531703	2025-12-27 09:23:58	2025-12-27 09:23:58
019b5d9e-acbe-737c-b897-24b1421cdaf4	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	d08bf7f0-18e2-46dc-963b-b8be84b15673	task	Tugas baru ditugaskan	Buat Laporan kas Desember	Finalisasi · Keuangan	App\\Models\\Task	42bcbf66-a6f0-45a9-a34b-dfb064e838da	de990493-03e6-4097-947d-851240d1cc0b	f	\N	http://127.0.0.1:8000/kanban-tugas/d08bf7f0-18e2-46dc-963b-b8be84b15673?task=42bcbf66-a6f0-45a9-a34b-dfb064e838da	2025-12-27 09:23:58	2025-12-27 09:23:58
\.


--
-- TOC entry 4235 (class 0 OID 22580)
-- Dependencies: 247
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, email, otp, type, expires_at, is_used, created_at, updated_at) FROM stdin;
1	rendyfoto10@gmail.com	610237	register	2025-12-10 15:39:49	t	2025-12-10 15:29:49	2025-12-10 15:30:23
2	ardhaniishere@gmail.com	942683	register	2025-12-22 11:32:40	t	2025-12-22 11:22:40	2025-12-22 11:23:01
\.


--
-- TOC entry 4237 (class 0 OID 22586)
-- Dependencies: 249
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.plans (id, plan_name, price_monthly, base_user_limit, description, is_active, created_at, updated_at) FROM stdin;
fa98db3e-08a0-4d96-9d67-29695da5bf40	Paket Basic	15000.00	5	Cocok untuk tim kecil	t	2025-12-19 18:47:20	2025-12-19 18:47:20
beafe811-6952-4b23-94e5-a24a5cdbd717	Paket Standard	45000.00	20	Untuk tim yang berkembang	t	2025-12-19 18:47:20	2025-12-19 18:47:20
1885200c-f308-46c7-913f-798715109d24	Paket Business	100000.00	50	Untuk organisasi besar	t	2025-12-19 18:47:20	2025-12-19 18:47:20
\.


--
-- TOC entry 4238 (class 0 OID 22593)
-- Dependencies: 250
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name) FROM stdin;
11111111-1111-1111-1111-111111111111	SuperAdmin
a688ef38-3030-45cb-9a4d-0407605bc322	Manager
33333333-3333-3333-3333-333333333333	AdminSistem
ed81bd39-9041-43b8-a504-bf743b5c2919	Member
55555555-5555-5555-5555-555555555555	Administrator
\.


--
-- TOC entry 4239 (class 0 OID 22597)
-- Dependencies: 251
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
6oOTkviTak6jxHXbgduzSDnFZYzJS4fsYySZemIX	de990493-03e6-4097-947d-851240d1cc0b	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36	YTo4OntzOjY6Il90b2tlbiI7czo0MDoiSDRoTFR5ME56aEJlcTdQNUtLbU91MTlFSXFqOUZjU2hyckphNFdZZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90YXNrcy9jb2xvcnMiO3M6NToicm91dGUiO047fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiJkZTk5MDQ5My0wM2U2LTQwOTctOTQ3ZC04NTEyNDBkMWNjMGIiO3M6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtzOjM2OiI5NGNjYmU3Mi05MGI4LTQ4ZTEtYjMzNC1mODI3N2UxNzM5ZDMiO3M6MjA6Imhhc19sb2dnZWRfaW5fYmVmb3JlIjtiOjE7czoyMDoiY3VycmVudF93b3Jrc3BhY2VfaWQiO3M6MzY6ImQwOGJmN2YwLTE4ZTItNDZkYy05NjNiLWI4YmU4NGIxNTY3MyI7czoyMjoiY3VycmVudF93b3Jrc3BhY2VfbmFtZSI7czo4OiJLZXVhbmdhbiI7fQ==	1766804332
\.


--
-- TOC entry 4240 (class 0 OID 22602)
-- Dependencies: 252
-- Data for Name: subscription_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscription_invoices (id, subscription_id, external_id, payment_url, amount, billing_month, status, paid_at, payment_details, created_at, updated_at, payment_method, proof_of_payment, admin_notes, verified_at, verified_by, payer_name, payer_bank, payer_account_number) FROM stdin;
019b3a11-b2e3-7281-8ba6-19e3ac324ccb	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766205797-799	\N	45000.00	2025-12	pending	\N	{"plan_id":"beafe811-6952-4b23-94e5-a24a5cdbd717","new_addon_count":0,"new_total_limit":20}	2025-12-20 11:43:17	2025-12-20 11:43:17	manual	\N	\N	\N	\N	\N	\N	\N
019b3677-e00e-723c-afde-2fe3d1e0a69e	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766145384-253	\N	100000.00	2025-12	pending	\N	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 18:56:24	2025-12-19 18:56:24	manual	\N	\N	\N	\N	\N	\N	\N
019b3a20-2ed6-72a4-b91d-38e1381e6d2c	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766206746-783	\N	45000.00	2025-12	pending	\N	{"plan_id":"beafe811-6952-4b23-94e5-a24a5cdbd717","new_addon_count":0,"new_total_limit":20}	2025-12-20 11:59:06	2025-12-20 11:59:06	manual	\N	\N	\N	\N	\N	\N	\N
019b3678-2546-73f1-8875-567da51843c3	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766145402-531	\N	100000.00	2025-12	paid	2025-12-19 18:57:53	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 18:56:42	2025-12-19 18:57:53	manual	payment_proofs/proof_019b3678-2546-73f1-8875-567da51843c3_1766145416.jpg	Disetujui oleh admin	2025-12-19 18:57:53	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3679-7e16-71d5-9fd6-c793897fce45	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766145490-410	\N	15000.00	2025-12	pending	\N	{"plan_id":"fa98db3e-08a0-4d96-9d67-29695da5bf40","new_addon_count":0,"new_total_limit":5}	2025-12-19 18:58:10	2025-12-19 18:58:10	manual	\N	\N	\N	\N	\N	\N	\N
019b3679-e48f-7060-b8d1-96fdbf74f877	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766145516-239	\N	15000.00	2025-12	paid	2025-12-19 18:58:55	{"plan_id":"fa98db3e-08a0-4d96-9d67-29695da5bf40","new_addon_count":0,"new_total_limit":5}	2025-12-19 18:58:36	2025-12-19 18:58:55	manual	payment_proofs/proof_019b3679-e48f-7060-b8d1-96fdbf74f877_1766145523.jpg	Disetujui oleh admin	2025-12-19 18:58:55	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3a21-3092-7106-b4ef-b5ea704fbd85	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766206812-833	\N	15000.00	2025-12	paid	2025-12-20 12:00:43	{"plan_id":"fa98db3e-08a0-4d96-9d67-29695da5bf40","new_addon_count":0,"new_total_limit":5}	2025-12-20 12:00:12	2025-12-20 12:00:43	manual	payment_proofs/proof_019b3a21-3092-7106-b4ef-b5ea704fbd85_1766206823.jpg	Disetujui oleh admin	2025-12-20 12:00:43	ac36859f-b56b-491b-92de-a7ea0c95cd6f	ada	BCA	123
019b3687-9d47-72d4-be19-4e64b74b3758	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766146415-744	\N	100000.00	2025-12	paid	2025-12-19 19:22:42	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 19:13:35	2025-12-19 19:22:42	manual	payment_proofs/proof_019b3687-9d47-72d4-be19-4e64b74b3758_1766146422.jpg	Disetujui oleh admin	2025-12-19 19:22:42	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3682-1485-7080-8fad-20c2845692ae	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766146053-621	\N	100000.00	2025-12	failed	\N	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 19:07:33	2025-12-19 19:22:58	manual	payment_proofs/proof_019b3682-1485-7080-8fad-20c2845692ae_1766146059.jpg	aldi	2025-12-19 19:22:58	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b366f-d90b-72b3-9701-c4859f647d90	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766144858-535	\N	100000.00	2025-12	failed	\N	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 18:47:38	2025-12-19 19:23:06	manual	payment_proofs/proof_019b366f-d90b-72b3-9701-c4859f647d90_1766144867.jpg	aldi punya cewe\\	2025-12-19 19:23:06	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3691-05af-7060-a353-cd907c829a35	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766147032-810	\N	45000.00	2025-12	failed	\N	{"plan_id":"beafe811-6952-4b23-94e5-a24a5cdbd717","new_addon_count":0,"new_total_limit":20}	2025-12-19 19:23:52	2025-12-19 19:24:13	manual	payment_proofs/proof_019b3691-05af-7060-a353-cd907c829a35_1766147038.jpg	aldi punya cewe\\	2025-12-19 19:24:13	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3698-9f92-720f-87ae-0b2243c5eec3	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766147530-691	\N	100000.00	2025-12	failed	\N	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-19 19:32:10	2025-12-19 19:32:31	manual	payment_proofs/proof_019b3698-9f92-720f-87ae-0b2243c5eec3_1766147536.jpg	aldi punya cewe	2025-12-19 19:32:31	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b3a11-4fbb-737c-a50e-c25c12ea76e8	019b366f-d904-7128-84dc-73eebfe6f955	INV-1766205771-893	\N	100000.00	2025-12	paid	2025-12-20 11:43:08	{"plan_id":"1885200c-f308-46c7-913f-798715109d24","new_addon_count":0,"new_total_limit":50}	2025-12-20 11:42:51	2025-12-20 11:43:08	manual	payment_proofs/proof_019b3a11-4fbb-737c-a50e-c25c12ea76e8_1766205776.png	Disetujui oleh admin	2025-12-20 11:43:08	ac36859f-b56b-491b-92de-a7ea0c95cd6f	\N	\N	\N
019b45e5-3158-701b-8c43-44a52e667b93	019b45e5-314c-7252-912c-ceb300ed7c0b	INV-1766404206-407	\N	45000.00	2025-12	pending	\N	{"plan_id":"beafe811-6952-4b23-94e5-a24a5cdbd717","new_addon_count":0,"new_total_limit":20}	2025-12-22 18:50:06	2025-12-22 18:50:06	manual	\N	\N	\N	\N	\N	\N	\N
\.


--
-- TOC entry 4241 (class 0 OID 22611)
-- Dependencies: 253
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscriptions (id, company_id, plan_id, addons_user_count, total_user_limit, start_date, end_date, status, created_at, updated_at, deleted_at) FROM stdin;
019b366f-d904-7128-84dc-73eebfe6f955	24015291-37bb-4357-bee7-4f28ad7e7c8c	fa98db3e-08a0-4d96-9d67-29695da5bf40	0	310	2025-12-19 18:47:38	2026-09-26 18:47:38	active	2025-12-19 18:47:38	2025-12-20 12:00:43	\N
019b45e5-314c-7252-912c-ceb300ed7c0b	94ccbe72-90b8-48e1-b334-f8277e1739d3	\N	0	5	2025-12-22 18:50:06	2025-12-29 18:50:06	trial	2025-12-22 18:50:06	2025-12-22 18:50:06	\N
\.


--
-- TOC entry 4242 (class 0 OID 22619)
-- Dependencies: 254
-- Data for Name: task_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_assignments (id, task_id, user_id, assigned_at) FROM stdin;
82d6a8e7-9559-4827-a903-df67b31b92a8	830a7562-8889-452a-a46e-71652d4c4b93	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-19 15:13:33
e818e6ca-6456-4c94-ad3c-3d73713dabb4	2d723848-0b2b-4804-8db7-424f0775b169	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-22 09:41:50
e3ad25d4-a5bd-42ec-ae69-c6ec8339c6b7	abb225ff-4d9b-4440-a6d8-355f393a809a	37e80fa7-8b99-49fe-8f94-162af6b33a67	2025-12-22 10:02:35
704b3e8f-de37-40ff-ba45-7fa7941b5755	c980fb2b-2f2b-4c6e-853e-4747b2aa7e42	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-23 12:34:38
3b9cf867-c799-438a-b944-43ceabf3ec02	b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-24 10:44:17
7b9625b7-bba2-47a7-8a77-6c884b3ad789	cc8807e5-adea-4591-b3e3-96fbdeb1e70e	cf643231-ae73-4a00-b1cb-ec58544cf590	2025-12-27 09:22:55
99ba4488-7e80-46ae-9903-26fa38fbe825	6a69f730-4fd8-4ce1-bbeb-2ff8f5531703	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-27 09:23:58
1b2997f6-8008-4639-b7ea-3bc3cd41ad90	42bcbf66-a6f0-45a9-a34b-dfb064e838da	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	2025-12-27 09:23:58
\.


--
-- TOC entry 4243 (class 0 OID 22624)
-- Dependencies: 255
-- Data for Name: task_labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_labels (task_id, label_id, created_at, updated_at) FROM stdin;
830a7562-8889-452a-a46e-71652d4c4b93	86644765-e407-4738-9fa8-1bdf1ccf1fb2	2025-12-10 15:33:54	2025-12-10 15:33:54
abb225ff-4d9b-4440-a6d8-355f393a809a	86644765-e407-4738-9fa8-1bdf1ccf1fb2	2025-12-22 10:02:35	2025-12-22 10:02:35
\.


--
-- TOC entry 4244 (class 0 OID 22627)
-- Dependencies: 256
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tasks (id, workspace_id, created_by, title, description, status, board_column_id, priority, is_secret, start_datetime, due_datetime, created_at, updated_at, deleted_at, phase, completed_at) FROM stdin;
830a7562-8889-452a-a46e-71652d4c4b93	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	aaa	<p>aasd</p>	inprogress	8f3bb9d7-fa98-4805-bae0-54c528c3ddeb	medium	t	2025-12-10 15:33:00	2025-12-11 15:33:00	2025-12-10 15:33:54	2025-12-16 14:08:11	\N	aaa	\N
2d723848-0b2b-4804-8db7-424f0775b169	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	ppp	\N	todo	facace9e-f86b-4cc0-bfee-9018a583deb6	medium	f	2025-12-22 15:13:00	2025-12-29 15:13:00	2025-12-19 15:13:44	2025-12-22 09:41:12	\N	222	\N
abb225ff-4d9b-4440-a6d8-355f393a809a	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	fdad37f2-c107-4473-893e-0e729c881a4b	aadalah	<p>sss</p>	todo	facace9e-f86b-4cc0-bfee-9018a583deb6	medium	f	2025-12-22 10:02:00	2025-12-30 10:02:00	2025-12-22 10:02:35	2025-12-22 10:02:35	\N	12	\N
c980fb2b-2f2b-4c6e-853e-4747b2aa7e42	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	afsd	<p>sadf</p>	todo	cde0745f-f768-4989-b023-e08409f318ce	medium	f	2025-12-23 15:31:00	2025-12-26 12:32:00	2025-12-23 12:32:04	2025-12-24 10:44:01	\N	\N	\N
b78eebd4-e016-4d4c-94c4-8cfaae2fafe3	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	adf	<p>dfs</p>	inprogress	7f48ee40-7faa-41b5-bf1f-119e84d75692	medium	f	2025-12-22 17:45:00	2025-12-27 11:45:00	2025-12-22 11:45:18	2025-12-24 10:44:17	\N	adfs	\N
1a91b0c5-8556-4ee8-a4d8-b5e00fad1d55	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	sss	<p>ss</p>	done	95b76a81-851f-4ec8-ba33-912f8520a2be	medium	f	2025-12-22 14:47:00	2025-12-28 18:47:00	2025-12-22 14:47:41	2025-12-24 10:49:45	2025-12-24 10:49:45	\N	2025-12-24 10:44:05+07
52371da7-19d5-4946-a8d9-7b2a4dc69241	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	kjh	<p>yoi</p>	cancel	ce19934b-6887-44fb-b670-d333972118fc	medium	f	2025-12-23 10:50:00	2025-12-26 10:52:00	2025-12-24 10:46:16	2025-12-24 10:55:20	\N	\N	\N
6fd44bea-db36-4664-853a-645d60f4b37d	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	sss	<p>op</p>	done	95b76a81-851f-4ec8-ba33-912f8520a2be	medium	f	2025-12-22 14:47:00	2025-12-22 18:47:00	2025-12-22 14:47:41	2025-12-24 11:04:42	\N	\N	2025-12-24 11:04:35+07
c0dbb4b8-5845-4bc3-8ec4-cb9c09cfce87	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	lkj	<p>lj</p>	todo	cde0745f-f768-4989-b023-e08409f318ce	medium	f	2025-12-26 13:02:00	2025-12-27 13:02:00	2025-12-27 09:02:24	2025-12-27 09:02:24	\N	\N	\N
4c85972c-4b44-4582-9e9d-0f1f9a27d2e2	a8e2b296-6d9b-4563-b502-103b35c3e134	de990493-03e6-4097-947d-851240d1cc0b	,mnk	<p>klj</p>	cancel	ce19934b-6887-44fb-b670-d333972118fc	medium	f	2025-12-24 09:02:00	2025-12-29 12:02:00	2025-12-27 09:02:45	2025-12-27 09:02:45	\N	\N	\N
cc8807e5-adea-4591-b3e3-96fbdeb1e70e	d08bf7f0-18e2-46dc-963b-b8be84b15673	de990493-03e6-4097-947d-851240d1cc0b	Review Berkas Transaksi	<p>Segera yah</p>	todo	8fd146af-d923-4637-aba2-54690ba1827b	medium	f	2025-12-28 07:22:00	2025-12-31 12:22:00	2025-12-27 09:22:55	2025-12-27 09:22:55	\N	\N	\N
42bcbf66-a6f0-45a9-a34b-dfb064e838da	d08bf7f0-18e2-46dc-963b-b8be84b15673	de990493-03e6-4097-947d-851240d1cc0b	Buat Laporan kas Desember	<p>Dari 2 Desember sampai akhir tahun yah</p>	todo	8fd146af-d923-4637-aba2-54690ba1827b	medium	f	2025-12-28 11:23:00	2025-12-31 00:00:00	2025-12-27 09:23:58	2025-12-27 09:24:10	2025-12-27 09:24:10	Finalisasi	\N
6a69f730-4fd8-4ce1-bbeb-2ff8f5531703	d08bf7f0-18e2-46dc-963b-b8be84b15673	de990493-03e6-4097-947d-851240d1cc0b	Buat Laporan kas Desember	<p>Dari 2 Desember sampai akhir tahun yah</p>	pending	55ca8e78-6876-4028-a249-beff4b5c09a6	medium	f	2025-12-28 11:23:00	2025-12-31 00:00:00	2025-12-27 09:23:58	2025-12-27 09:24:44	\N	Finalisasi	\N
\.


--
-- TOC entry 4245 (class 0 OID 22636)
-- Dependencies: 257
-- Data for Name: user_companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_companies (id, user_id, company_id, roles_id, created_at, updated_at, deleted_at) FROM stdin;
1594c680-c30e-4e1a-90b8-f42c8c355c16	fdad37f2-c107-4473-893e-0e729c881a4b	24015291-37bb-4357-bee7-4f28ad7e7c8c	11111111-1111-1111-1111-111111111111	2025-12-10 15:28:28	2025-12-10 15:28:28	\N
6ae9762a-61da-47d6-b5b1-481c52bc488d	37e80fa7-8b99-49fe-8f94-162af6b33a67	24015291-37bb-4357-bee7-4f28ad7e7c8c	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-10 15:30:41	2025-12-10 15:30:41	\N
759b173c-e653-48af-b868-c428fc9f959b	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	94ccbe72-90b8-48e1-b334-f8277e1739d3	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-22 11:23:20	2025-12-22 11:23:20	\N
a9213d2a-72f9-4bf0-b059-1704afe271d8	cf643231-ae73-4a00-b1cb-ec58544cf590	94ccbe72-90b8-48e1-b334-f8277e1739d3	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-22 15:33:28	2025-12-22 15:33:28	\N
63fef160-61aa-4065-af68-93c60a36b1ee	de990493-03e6-4097-947d-851240d1cc0b	94ccbe72-90b8-48e1-b334-f8277e1739d3	11111111-1111-1111-1111-111111111111	2025-12-22 11:20:55	2025-12-22 11:20:55	\N
\.


--
-- TOC entry 4246 (class 0 OID 22642)
-- Dependencies: 258
-- Data for Name: user_workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_workspaces (id, user_id, workspace_id, roles_id, join_date, status_active, updated_at, created_at) FROM stdin;
7d189396-71b7-4a3f-98f3-302529f1c1bc	37e80fa7-8b99-49fe-8f94-162af6b33a67	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-10 15:32:47	t	2025-12-10 15:32:47	2025-12-10 15:32:47
d051f2f8-2cf3-46b1-bbfb-6d757a35a64a	cf643231-ae73-4a00-b1cb-ec58544cf590	a8e2b296-6d9b-4563-b502-103b35c3e134	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-22 15:34:02	t	2025-12-22 15:34:02	2025-12-22 15:34:02
d4ba1392-69aa-4717-a497-6be95fd690e5	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	a8e2b296-6d9b-4563-b502-103b35c3e134	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-22 11:24:05	t	2025-12-22 11:24:05	2025-12-22 11:24:05
4748bee9-30c8-4ccd-bfac-e724e12c39ef	cf643231-ae73-4a00-b1cb-ec58544cf590	d08bf7f0-18e2-46dc-963b-b8be84b15673	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-27 09:21:54	t	2025-12-27 09:21:54	2025-12-27 09:21:54
6e47c982-d824-4340-8577-797ef873d9f0	63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	d08bf7f0-18e2-46dc-963b-b8be84b15673	ed81bd39-9041-43b8-a504-bf743b5c2919	2025-12-27 09:21:54	t	2025-12-27 09:21:54	2025-12-27 09:21:54
\.


--
-- TOC entry 4247 (class 0 OID 22650)
-- Dependencies: 259
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, full_name, email, password, google_id, status_active, created_at, updated_at, deleted_at, avatar, email_verified_at, onboarding_step, has_seen_onboarding, onboarding_type, system_role_id) FROM stdin;
ac36859f-b56b-491b-92de-a7ea0c95cd6f	Admin Sistem Koladi	admin@koladi.com	$2y$12$4K3a/R9rLROmhPESGkjdN.Gn9SfURV5q5E0mNsw/qO/4mJEhzlXMa	\N	t	2025-12-10 15:25:36	2025-12-10 15:25:36	\N	\N	2025-12-10 15:25:36	\N	f	\N	33333333-3333-3333-3333-333333333333
fdad37f2-c107-4473-893e-0e729c881a4b	Kuliah	kuliahbisa2005@gmail.com	$2y$12$.8OYO8bw1K/jTIOeUnKXSuvD.j2P0A0FYEgwQkCah1ddfBMOMnU2q	117185357584893816951	t	2025-12-10 15:27:46	2025-12-10 15:28:44	\N	\N	\N	workspace-created	f	full	\N
37e80fa7-8b99-49fe-8f94-162af6b33a67	Rivaldi	rendyfoto10@gmail.com	$2y$12$E/Az5uNrQzUFHKOd/kiYQuYMvhIH2irEWZMkpUwgV7DmvSMplNl4i	101543583273495613457	t	2025-12-10 15:30:23	2025-12-16 14:06:27	\N	\N	\N	\N	t	member	\N
cf643231-ae73-4a00-b1cb-ec58544cf590	Naufal	naufal201080@gmail.com	$2y$12$FpOfae1s1tuFREHUJKITwuXg55CfwS2Illqps62DdS1AZqarpBWVu	110002422326815171139	t	2025-12-22 15:33:21	2025-12-22 15:33:52	\N	\N	\N	\N	t	member	\N
de990493-03e6-4097-947d-851240d1cc0b	kocak	naufalardhanijapan@gmail.com	$2y$12$2tx3krxQVkSqRa9RPdoMzeCe8kcDcgyIajlWGsxM30Zp6Avk.MRRy	103578037043880658374	t	2025-12-22 11:20:46	2025-12-23 12:22:45	\N	\N	\N	\N	t	full	\N
63a8b30b-3d57-4a50-8eb3-7a56abe2cebf	Siti	ardhaniishere@gmail.com	$2y$12$MZCSltXPLRWSp/6TcztdTOONrQUhsdiMgg1LVy/SDcoZFrO0rMTNC	107669652105985877580	t	2025-12-22 11:23:01	2025-12-23 11:16:59	\N	avatars/3Rqqv2IMqlBgCvNwwZTyMLP90puTfJ91ljmu0DiL.jpg	\N	\N	t	member	\N
\.


--
-- TOC entry 4248 (class 0 OID 22660)
-- Dependencies: 260
-- Data for Name: workspace_performance_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspace_performance_snapshots (id, workspace_id, period_start, period_end, period_type, metrics, performance_score, quality_score, risk_score, suggestions, created_at, updated_at, version) FROM stdin;
019b35ac-49ff-727b-9902-637b3aae14a6	82ebb561-7977-44b2-9f2d-6fb732187a30	2025-12-15	2025-12-21	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2025-12-19 15:14:02	2025-12-19 15:14:02	2.0
019b35a9-f937-73ac-ba1f-aa90622e77cf	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	2025-12-15	2025-12-21	week	{"gini": 0, "wipRate": 50, "avgDelay": 0, "idleRate": 50, "maxDelay": 0, "riskScore": 100, "onTimeRate": 0, "totalTasks": 2, "avgProgress": 50, "medianDelay": 0, "memberCount": 1, "overdueRate": 50, "maxLoadRatio": 1, "overdueCount": 1, "qualityScore": 10, "taskVelocity": 0, "avgDelayCapped": 0, "completionRate": 0, "tasksPerMember": 1, "workspacePhase": "stagnant", "urgentTaskRatio": 100, "performanceScore": 20, "avgTimeToDeadline": -4.4, "criticalTaskRatio": 100, "deadlineAdherence": 0, "hasCompletedTasks": false, "lateCompletionRate": 0}	20	10	100	{"actions": ["Review tugas yang terlambat dan cari solusi", "Prioritaskan tugas dengan deadline terdekat", "Evaluasi dan mitigasi risiko workspace", "Assign tugas ke anggota", "Mulai tugas prioritas tinggi"], "warning": [{"title": "Banyak tugas belum dimulai", "value": "50%", "metric": "idleRate", "description": "1 tugas masih menunggu dikerjakan", "suggestions": ["Assign tugas ke anggota", "Mulai tugas prioritas tinggi", "Cek apakah ada hambatan untuk mulai"]}], "critical": [{"title": "Workspace tidak aktif", "value": "stagnant", "metric": "workspacePhase", "priority": 1, "description": "Banyak tugas terlambat tapi belum ada yang selesai"}, {"title": "Banyak tugas terlambat dan sedikit yang selesai", "value": "50%", "metric": "overdueRate", "actions": ["Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan utama yang memperlambat tim", "Reschedule deadline jika memang tidak realistis"], "priority": 1, "description": "1 tugas melewati deadline, hanya 0% yang selesai"}, {"title": "Banyak deadline mendesak", "value": "100%", "metric": "urgentTaskRatio", "actions": ["PRIORITY: Selesaikan tugas overdue terlebih dahulu", "Daily check-in untuk monitor blocker", "Escalate jika ada dependency issue"], "priority": 1, "description": "1 tugas sudah terlambat, 1 tugas deadline dalam 24 jam — Ada masalah eksekusi yang perlu segera diatasi"}, {"title": "Tingkat risiko tinggi", "value": "100/100", "metric": "riskScore", "priority": 1, "description": "Workspace berisiko gagal mencapai target (skor risiko: 100/100)"}], "positive": [], "empty_state": false}	2025-12-19 15:11:30	2025-12-20 12:09:44	2.0
019b43e0-4b55-7091-9d38-b2fee6db1025	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	2025-12-22	2025-12-28	week	{"gini": 0, "wipRate": 50, "avgDelay": 0, "idleRate": 50, "maxDelay": 0, "riskScore": 100, "onTimeRate": 0, "totalTasks": 2, "avgProgress": 50, "medianDelay": 0, "memberCount": 1, "overdueRate": 100, "maxLoadRatio": 1, "overdueCount": 2, "qualityScore": 0, "taskVelocity": 0, "avgDelayCapped": 0, "completionRate": 0, "tasksPerMember": 1, "workspacePhase": "stagnant", "urgentTaskRatio": 100, "performanceScore": 20, "avgTimeToDeadline": -6.3, "criticalTaskRatio": 100, "deadlineAdherence": 0, "hasCompletedTasks": false, "lateCompletionRate": 0}	20	0	100	{"actions": ["Review tugas yang terlambat dan cari solusi", "Prioritaskan tugas dengan deadline terdekat", "Evaluasi dan mitigasi risiko workspace", "Assign tugas ke anggota", "Mulai tugas prioritas tinggi"], "warning": [{"title": "Banyak tugas belum dimulai", "value": "50%", "metric": "idleRate", "description": "1 tugas masih menunggu dikerjakan", "suggestions": ["Assign tugas ke anggota", "Mulai tugas prioritas tinggi", "Cek apakah ada hambatan untuk mulai"]}], "critical": [{"title": "Workspace tidak aktif", "value": "stagnant", "metric": "workspacePhase", "priority": 1, "description": "Banyak tugas terlambat tapi belum ada yang selesai"}, {"title": "Banyak tugas terlambat dan sedikit yang selesai", "value": "100%", "metric": "overdueRate", "actions": ["Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan utama yang memperlambat tim", "Reschedule deadline jika memang tidak realistis"], "priority": 1, "description": "2 tugas melewati deadline, hanya 0% yang selesai"}, {"title": "Banyak deadline mendesak", "value": "100%", "metric": "urgentTaskRatio", "actions": ["URGENT: Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan yang membuat tugas terlambat", "Pertimbangkan reschedule jika deadline tidak realistis"], "priority": 1, "description": "2 tugas sudah terlambat — Tim kesulitan mengejar deadline"}, {"title": "Tingkat risiko tinggi", "value": "100/100", "metric": "riskScore", "priority": 1, "description": "Workspace berisiko gagal mencapai target (skor risiko: 100/100)"}], "positive": [], "empty_state": false}	2025-12-22 09:25:31	2025-12-22 09:25:31	2.0
019b43ed-f215-72c3-bb0a-9dd4833c790e	82ebb561-7977-44b2-9f2d-6fb732187a30	2025-12-22	2025-12-28	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2025-12-22 09:40:26	2025-12-22 09:40:26	2.0
019b442d-1bbd-7154-83fc-38b038435618	c6394c7b-a46d-44d9-aabc-348f2b8e69c0	2025-12-01	2025-12-31	month	{"gini": 0, "wipRate": 33.3, "avgDelay": 0, "idleRate": 66.7, "maxDelay": 0, "riskScore": 45, "onTimeRate": 0, "totalTasks": 3, "avgProgress": 33.3, "medianDelay": 0, "memberCount": 1, "overdueRate": 33.3, "maxLoadRatio": 1, "overdueCount": 1, "qualityScore": 13, "taskVelocity": 0, "avgDelayCapped": 0, "completionRate": 0, "tasksPerMember": 3, "workspacePhase": "new", "urgentTaskRatio": 33.3, "performanceScore": 50, "avgTimeToDeadline": 1.4, "criticalTaskRatio": 33.3, "deadlineAdherence": 0, "hasCompletedTasks": false, "lateCompletionRate": 0}	50	13	45	{"actions": ["Review tugas yang terlambat dan cari solusi", "Prioritaskan tugas dengan deadline terdekat", "Prioritaskan tugas yang sudah overdue", "Review apakah deadline realistis"], "warning": [{"title": "Ada tugas melewati deadline", "value": "33.3%", "metric": "overdueRate", "description": "1 tugas sudah lewat deadline tapi belum selesai", "suggestions": ["Prioritaskan tugas yang sudah overdue", "Review apakah deadline realistis", "Cek hambatan yang menghambat tim"]}], "critical": [{"title": "Banyak tugas terlambat dan sedikit yang selesai", "value": "33.3%", "metric": "overdueRate", "actions": ["Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan utama yang memperlambat tim", "Reschedule deadline jika memang tidak realistis"], "priority": 1, "description": "1 tugas melewati deadline, hanya 0% yang selesai"}, {"title": "Banyak deadline mendesak", "value": "33.3%", "metric": "urgentTaskRatio", "actions": ["PRIORITY: Selesaikan tugas overdue terlebih dahulu", "Daily check-in untuk monitor blocker", "Escalate jika ada dependency issue"], "priority": 1, "description": "1 tugas sudah terlambat — Ada masalah eksekusi yang perlu segera diatasi"}], "positive": [{"title": "Workspace siap dimulai", "value": 3, "metric": "totalTasks", "description": "Sudah ada 3 tugas terdaftar dan mulai dikerjakan. Momentum bagus!"}, {"title": "Tim mulai produktif", "value": "33.3%", "metric": "wipRate", "description": "1 tugas sudah dikerjakan. Teruskan sampai selesai!"}], "empty_state": false}	2025-12-22 10:49:25	2025-12-22 10:49:25	2.0
019b444d-384d-70cc-818c-7feb379c3740	a8e2b296-6d9b-4563-b502-103b35c3e134	2025-12-22	2025-12-28	week	{"gini": 0, "wipRate": 25, "avgDelay": 0, "idleRate": 25, "maxDelay": 0, "riskScore": 80, "onTimeRate": 50, "totalTasks": 4, "avgProgress": 0, "medianDelay": 0, "memberCount": 1, "overdueRate": 25, "maxLoadRatio": 1, "overdueCount": 1, "qualityScore": 55, "taskVelocity": -1.1100000000000000976996261670137755572795867919921875, "avgDelayCapped": 0, "completionRate": 50, "tasksPerMember": 2, "workspacePhase": "active", "urgentTaskRatio": 50, "performanceScore": 29, "avgTimeToDeadline": 0.8000000000000000444089209850062616169452667236328125, "criticalTaskRatio": 50, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 0}	29	55	80	{"actions": ["Prioritaskan tugas dengan deadline terdekat", "Evaluasi dan mitigasi risiko workspace"], "warning": [], "critical": [{"title": "Banyak deadline mendesak", "value": "50%", "metric": "urgentTaskRatio", "actions": ["PRIORITY: Selesaikan tugas overdue terlebih dahulu", "Daily check-in untuk monitor blocker", "Escalate jika ada dependency issue"], "priority": 1, "description": "1 tugas sudah terlambat — Ada masalah eksekusi yang perlu segera diatasi"}, {"title": "Tingkat risiko tinggi", "value": "80/100", "metric": "riskScore", "priority": 1, "description": "Workspace berisiko gagal mencapai target (skor risiko: 80/100)"}], "positive": [], "empty_state": false}	2025-12-22 11:24:30	2025-12-24 09:00:28	2.0
\.


--
-- TOC entry 4249 (class 0 OID 22673)
-- Dependencies: 261
-- Data for Name: workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspaces (id, company_id, type, name, created_by, created_at, updated_at, deleted_at, description) FROM stdin;
c6394c7b-a46d-44d9-aabc-348f2b8e69c0	24015291-37bb-4357-bee7-4f28ad7e7c8c	Tim	Koladi	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:28:48	2025-12-10 15:28:48	\N	ss
82ebb561-7977-44b2-9f2d-6fb732187a30	24015291-37bb-4357-bee7-4f28ad7e7c8c	Proyek	popo	fdad37f2-c107-4473-893e-0e729c881a4b	2025-12-10 15:31:40	2025-12-10 15:31:40	\N	poop
a8e2b296-6d9b-4563-b502-103b35c3e134	94ccbe72-90b8-48e1-b334-f8277e1739d3	Tim	workspace 1	de990493-03e6-4097-947d-851240d1cc0b	2025-12-22 11:21:09	2025-12-22 11:21:09	\N	\N
d08bf7f0-18e2-46dc-963b-b8be84b15673	94ccbe72-90b8-48e1-b334-f8277e1739d3	Proyek	Keuangan	de990493-03e6-4097-947d-851240d1cc0b	2025-12-27 09:21:11	2025-12-27 09:21:11	\N	\N
\.


--
-- TOC entry 4261 (class 0 OID 0)
-- Dependencies: 233
-- Name: feedbacks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.feedbacks_id_seq', 1, true);


--
-- TOC entry 4262 (class 0 OID 0)
-- Dependencies: 243
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 5, true);


--
-- TOC entry 4263 (class 0 OID 0)
-- Dependencies: 248
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 2, true);


--
-- TOC entry 3864 (class 2606 OID 22685)
-- Name: addons addons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.addons
    ADD CONSTRAINT addons_pkey PRIMARY KEY (id);


--
-- TOC entry 3866 (class 2606 OID 22687)
-- Name: announcement_recipients announcement_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 3868 (class 2606 OID 22689)
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- TOC entry 3870 (class 2606 OID 22691)
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- TOC entry 3874 (class 2606 OID 22693)
-- Name: board_columns board_columns_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_pkey PRIMARY KEY (id);


--
-- TOC entry 3879 (class 2606 OID 22695)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 3877 (class 2606 OID 22697)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 3881 (class 2606 OID 22699)
-- Name: calendar_events calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


--
-- TOC entry 3883 (class 2606 OID 22701)
-- Name: calendar_participants calendar_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 3885 (class 2606 OID 22703)
-- Name: checklists checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_pkey PRIMARY KEY (id);


--
-- TOC entry 3888 (class 2606 OID 22705)
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- TOC entry 3890 (class 2606 OID 22707)
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- TOC entry 3892 (class 2606 OID 22709)
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- TOC entry 3894 (class 2606 OID 22711)
-- Name: conversation_participants conversation_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 3898 (class 2606 OID 22713)
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- TOC entry 3903 (class 2606 OID 22715)
-- Name: document_recipients document_recipients_document_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_document_id_user_id_unique UNIQUE (document_id, user_id);


--
-- TOC entry 3905 (class 2606 OID 22717)
-- Name: document_recipients document_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 3907 (class 2606 OID 22719)
-- Name: feedbacks feedbacks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks
    ADD CONSTRAINT feedbacks_pkey PRIMARY KEY (id);


--
-- TOC entry 3909 (class 2606 OID 22721)
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- TOC entry 3912 (class 2606 OID 22723)
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- TOC entry 3915 (class 2606 OID 22725)
-- Name: insight_recipients insight_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 3917 (class 2606 OID 22727)
-- Name: insights insights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_pkey PRIMARY KEY (id);


--
-- TOC entry 3919 (class 2606 OID 22729)
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- TOC entry 3921 (class 2606 OID 22731)
-- Name: labels labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (id);


--
-- TOC entry 3923 (class 2606 OID 22733)
-- Name: leave_requests leave_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_pkey PRIMARY KEY (id);


--
-- TOC entry 3926 (class 2606 OID 22735)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 3928 (class 2606 OID 22737)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 3932 (class 2606 OID 22739)
-- Name: mindmap_nodes mindmap_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_pkey PRIMARY KEY (id);


--
-- TOC entry 3934 (class 2606 OID 22741)
-- Name: mindmaps mindmaps_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_pkey PRIMARY KEY (id);


--
-- TOC entry 3937 (class 2606 OID 22743)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 3942 (class 2606 OID 22745)
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- TOC entry 3944 (class 2606 OID 22747)
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- TOC entry 3946 (class 2606 OID 22749)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 3948 (class 2606 OID 22751)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 3953 (class 2606 OID 22753)
-- Name: subscription_invoices subscription_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_pkey PRIMARY KEY (id);


--
-- TOC entry 3958 (class 2606 OID 22755)
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- TOC entry 3963 (class 2606 OID 22757)
-- Name: task_assignments task_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 3967 (class 2606 OID 22759)
-- Name: task_labels task_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_pkey PRIMARY KEY (task_id, label_id);


--
-- TOC entry 3974 (class 2606 OID 22761)
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- TOC entry 3976 (class 2606 OID 22763)
-- Name: user_companies user_companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_pkey PRIMARY KEY (id);


--
-- TOC entry 3978 (class 2606 OID 22765)
-- Name: user_workspaces user_workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 3980 (class 2606 OID 22767)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 3982 (class 2606 OID 22769)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3984 (class 2606 OID 22771)
-- Name: workspace_performance_snapshots workspace_performance_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT workspace_performance_snapshots_pkey PRIMARY KEY (id);


--
-- TOC entry 3987 (class 2606 OID 22773)
-- Name: workspaces workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 3899 (class 1259 OID 22774)
-- Name: conversations_scope_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_company_id_index ON public.conversations USING btree (scope, company_id);


--
-- TOC entry 3900 (class 1259 OID 22775)
-- Name: conversations_scope_workspace_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_workspace_id_index ON public.conversations USING btree (scope, workspace_id);


--
-- TOC entry 3871 (class 1259 OID 22776)
-- Name: idx_attachments_attachable; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_attachable ON public.attachments USING btree (attachable_type, attachable_id);


--
-- TOC entry 3872 (class 1259 OID 22777)
-- Name: idx_attachments_uploaded_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_uploaded_by ON public.attachments USING btree (uploaded_by);


--
-- TOC entry 3875 (class 1259 OID 23182)
-- Name: idx_board_columns_workspace_position; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_board_columns_workspace_position ON public.board_columns USING btree (workspace_id, "position");


--
-- TOC entry 3886 (class 1259 OID 23185)
-- Name: idx_checklists_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_checklists_task ON public.checklists USING btree (task_id);


--
-- TOC entry 3895 (class 1259 OID 22778)
-- Name: idx_conversation_participants_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_conversation_id ON public.conversation_participants USING btree (conversation_id);


--
-- TOC entry 3896 (class 1259 OID 22779)
-- Name: idx_conversation_participants_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_user_id ON public.conversation_participants USING btree (user_id);


--
-- TOC entry 3901 (class 1259 OID 22780)
-- Name: idx_conversations_last_message_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversations_last_message_id ON public.conversations USING btree (last_message_id);


--
-- TOC entry 3910 (class 1259 OID 22781)
-- Name: idx_files_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_files_company_id ON public.files USING btree (company_id);


--
-- TOC entry 3913 (class 1259 OID 22782)
-- Name: idx_folders_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_folders_company_id ON public.folders USING btree (company_id);


--
-- TOC entry 3924 (class 1259 OID 22783)
-- Name: idx_messages_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_messages_conversation_id ON public.messages USING btree (conversation_id);


--
-- TOC entry 3929 (class 1259 OID 22784)
-- Name: idx_mindmap_nodes_mindmap_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_mindmap_id ON public.mindmap_nodes USING btree (mindmap_id);


--
-- TOC entry 3930 (class 1259 OID 22785)
-- Name: idx_mindmap_nodes_parent_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_parent_id ON public.mindmap_nodes USING btree (parent_id);


--
-- TOC entry 3949 (class 1259 OID 22786)
-- Name: idx_subscription_invoices_payment_method; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_payment_method ON public.subscription_invoices USING btree (payment_method);


--
-- TOC entry 3950 (class 1259 OID 22787)
-- Name: idx_subscription_invoices_verified_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_verified_at ON public.subscription_invoices USING btree (verified_at);


--
-- TOC entry 3960 (class 1259 OID 23180)
-- Name: idx_task_assignments_task_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_task_user ON public.task_assignments USING btree (task_id, user_id);


--
-- TOC entry 3961 (class 1259 OID 23181)
-- Name: idx_task_assignments_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_user ON public.task_assignments USING btree (user_id);


--
-- TOC entry 3964 (class 1259 OID 23184)
-- Name: idx_task_labels_label; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_label ON public.task_labels USING btree (label_id);


--
-- TOC entry 3965 (class 1259 OID 23183)
-- Name: idx_task_labels_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_task ON public.task_labels USING btree (task_id);


--
-- TOC entry 3968 (class 1259 OID 23178)
-- Name: idx_tasks_due_datetime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_due_datetime ON public.tasks USING btree (due_datetime) WHERE (deleted_at IS NULL);


--
-- TOC entry 3969 (class 1259 OID 23179)
-- Name: idx_tasks_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_status ON public.tasks USING btree (status) WHERE (deleted_at IS NULL);


--
-- TOC entry 3970 (class 1259 OID 23175)
-- Name: idx_tasks_workspace_column; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_column ON public.tasks USING btree (workspace_id, board_column_id);


--
-- TOC entry 3971 (class 1259 OID 23177)
-- Name: idx_tasks_workspace_creator; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_creator ON public.tasks USING btree (workspace_id, created_by);


--
-- TOC entry 3972 (class 1259 OID 23176)
-- Name: idx_tasks_workspace_secret; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_secret ON public.tasks USING btree (workspace_id, is_secret);


--
-- TOC entry 3935 (class 1259 OID 22788)
-- Name: notifications_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_created_at_index ON public.notifications USING btree (created_at);


--
-- TOC entry 3938 (class 1259 OID 22789)
-- Name: notifications_type_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_type_user_id_index ON public.notifications USING btree (type, user_id);


--
-- TOC entry 3939 (class 1259 OID 22790)
-- Name: notifications_user_id_company_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_user_id_company_id_is_read_index ON public.notifications USING btree (user_id, company_id, is_read);


--
-- TOC entry 3940 (class 1259 OID 22791)
-- Name: otp_verifications_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_email_index ON public.otp_verifications USING btree (email);


--
-- TOC entry 3951 (class 1259 OID 22792)
-- Name: subscription_invoices_external_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_external_id_index ON public.subscription_invoices USING btree (external_id);


--
-- TOC entry 3954 (class 1259 OID 22793)
-- Name: subscription_invoices_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_status_index ON public.subscription_invoices USING btree (status);


--
-- TOC entry 3955 (class 1259 OID 22794)
-- Name: subscription_invoices_subscription_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_subscription_id_index ON public.subscription_invoices USING btree (subscription_id);


--
-- TOC entry 3956 (class 1259 OID 22795)
-- Name: subscriptions_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_company_id_index ON public.subscriptions USING btree (company_id);


--
-- TOC entry 3959 (class 1259 OID 22796)
-- Name: subscriptions_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_status_index ON public.subscriptions USING btree (status);


--
-- TOC entry 3985 (class 1259 OID 22797)
-- Name: ws_perf_idx_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ws_perf_idx_created_at ON public.workspace_performance_snapshots USING btree (created_at);


--
-- TOC entry 4060 (class 2620 OID 22798)
-- Name: invitations update_invitations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_invitations_updated_at BEFORE UPDATE ON public.invitations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3988 (class 2606 OID 22799)
-- Name: announcement_recipients announcement_recipients_announcement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_announcement_id_fkey FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- TOC entry 3989 (class 2606 OID 22804)
-- Name: announcement_recipients announcement_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3990 (class 2606 OID 22809)
-- Name: announcements announcements_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- TOC entry 3991 (class 2606 OID 22814)
-- Name: announcements announcements_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 3992 (class 2606 OID 22819)
-- Name: announcements announcements_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 3993 (class 2606 OID 22824)
-- Name: attachments attachments_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 3994 (class 2606 OID 22829)
-- Name: board_columns board_columns_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 3995 (class 2606 OID 22834)
-- Name: board_columns board_columns_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 3996 (class 2606 OID 22839)
-- Name: calendar_events calendar_events_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 3997 (class 2606 OID 22844)
-- Name: calendar_events calendar_events_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 3999 (class 2606 OID 22849)
-- Name: calendar_participants calendar_participants_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.calendar_events(id) ON DELETE CASCADE;


--
-- TOC entry 4000 (class 2606 OID 22854)
-- Name: calendar_participants calendar_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4001 (class 2606 OID 22859)
-- Name: checklists checklists_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 4002 (class 2606 OID 22864)
-- Name: comments comments_parent_comment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_parent_comment_id_fkey FOREIGN KEY (parent_comment_id) REFERENCES public.comments(id) ON DELETE CASCADE;


--
-- TOC entry 4003 (class 2606 OID 22869)
-- Name: comments comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 4004 (class 2606 OID 22874)
-- Name: conversation_participants conversation_participants_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 4005 (class 2606 OID 22879)
-- Name: conversation_participants conversation_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4006 (class 2606 OID 22884)
-- Name: conversations conversations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4007 (class 2606 OID 22889)
-- Name: conversations conversations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4008 (class 2606 OID 22894)
-- Name: conversations conversations_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 4009 (class 2606 OID 22899)
-- Name: conversations conversations_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id);


--
-- TOC entry 4011 (class 2606 OID 22904)
-- Name: files files_folder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- TOC entry 4012 (class 2606 OID 22909)
-- Name: files files_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 4013 (class 2606 OID 22914)
-- Name: files files_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 3998 (class 2606 OID 22919)
-- Name: calendar_events fk_calendar_events_company_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT fk_calendar_events_company_id FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4010 (class 2606 OID 22924)
-- Name: conversations fk_conversations_last_message; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT fk_conversations_last_message FOREIGN KEY (last_message_id) REFERENCES public.messages(id) ON DELETE SET NULL;


--
-- TOC entry 4014 (class 2606 OID 22929)
-- Name: files fk_files_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT fk_files_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4015 (class 2606 OID 22934)
-- Name: folders fk_folders_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_folders_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4039 (class 2606 OID 22939)
-- Name: subscription_invoices fk_subscription_invoices_verified_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT fk_subscription_invoices_verified_by FOREIGN KEY (verified_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 4057 (class 2606 OID 22944)
-- Name: workspace_performance_snapshots fk_wps_workspace; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT fk_wps_workspace FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4016 (class 2606 OID 22949)
-- Name: folders folders_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 4017 (class 2606 OID 22954)
-- Name: folders folders_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4018 (class 2606 OID 22959)
-- Name: insight_recipients insight_recipients_insight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_insight_id_fkey FOREIGN KEY (insight_id) REFERENCES public.insights(id) ON DELETE CASCADE;


--
-- TOC entry 4019 (class 2606 OID 22964)
-- Name: insight_recipients insight_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4020 (class 2606 OID 22969)
-- Name: insights insights_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 4021 (class 2606 OID 22974)
-- Name: insights insights_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4022 (class 2606 OID 22979)
-- Name: invitations invitations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id);


--
-- TOC entry 4023 (class 2606 OID 22984)
-- Name: invitations invitations_invited_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_invited_by_fkey FOREIGN KEY (invited_by) REFERENCES public.users(id);


--
-- TOC entry 4024 (class 2606 OID 22989)
-- Name: labels labels_color_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_color_id_fkey FOREIGN KEY (color_id) REFERENCES public.colors(id);


--
-- TOC entry 4025 (class 2606 OID 22994)
-- Name: leave_requests leave_requests_approved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- TOC entry 4026 (class 2606 OID 22999)
-- Name: leave_requests leave_requests_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4027 (class 2606 OID 23004)
-- Name: leave_requests leave_requests_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4028 (class 2606 OID 23009)
-- Name: messages messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 4029 (class 2606 OID 23014)
-- Name: messages messages_reply_to_message_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_reply_to_message_id_fkey FOREIGN KEY (reply_to_message_id) REFERENCES public.messages(id);


--
-- TOC entry 4030 (class 2606 OID 23019)
-- Name: messages messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id);


--
-- TOC entry 4031 (class 2606 OID 23024)
-- Name: mindmap_nodes mindmap_nodes_mindmap_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_mindmap_id_fkey FOREIGN KEY (mindmap_id) REFERENCES public.mindmaps(id) ON DELETE CASCADE;


--
-- TOC entry 4032 (class 2606 OID 23029)
-- Name: mindmap_nodes mindmap_nodes_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.mindmap_nodes(id) ON DELETE CASCADE;


--
-- TOC entry 4033 (class 2606 OID 23034)
-- Name: mindmaps mindmaps_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4034 (class 2606 OID 23039)
-- Name: notifications notifications_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 4035 (class 2606 OID 23044)
-- Name: notifications notifications_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4036 (class 2606 OID 23049)
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4037 (class 2606 OID 23054)
-- Name: notifications notifications_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4038 (class 2606 OID 23059)
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 4040 (class 2606 OID 23064)
-- Name: subscription_invoices subscription_invoices_subscription_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES public.subscriptions(id) ON DELETE CASCADE;


--
-- TOC entry 4041 (class 2606 OID 23069)
-- Name: subscriptions subscriptions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4042 (class 2606 OID 23074)
-- Name: subscriptions subscriptions_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- TOC entry 4043 (class 2606 OID 23079)
-- Name: task_assignments task_assignments_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 4044 (class 2606 OID 23084)
-- Name: task_assignments task_assignments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4045 (class 2606 OID 23089)
-- Name: task_labels task_labels_label_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_label_id_foreign FOREIGN KEY (label_id) REFERENCES public.labels(id) ON DELETE CASCADE;


--
-- TOC entry 4046 (class 2606 OID 23094)
-- Name: task_labels task_labels_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 4047 (class 2606 OID 23099)
-- Name: tasks tasks_board_column_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_board_column_id_fkey FOREIGN KEY (board_column_id) REFERENCES public.board_columns(id);


--
-- TOC entry 4048 (class 2606 OID 23104)
-- Name: tasks tasks_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 4049 (class 2606 OID 23109)
-- Name: tasks tasks_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4050 (class 2606 OID 23114)
-- Name: user_companies user_companies_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4051 (class 2606 OID 23119)
-- Name: user_companies user_companies_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 4052 (class 2606 OID 23124)
-- Name: user_companies user_companies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4053 (class 2606 OID 23129)
-- Name: user_workspaces user_workspaces_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 4054 (class 2606 OID 23134)
-- Name: user_workspaces user_workspaces_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4055 (class 2606 OID 23139)
-- Name: user_workspaces user_workspaces_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 4056 (class 2606 OID 23144)
-- Name: users users_system_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_system_role_id_foreign FOREIGN KEY (system_role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- TOC entry 4058 (class 2606 OID 23149)
-- Name: workspaces workspaces_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 4059 (class 2606 OID 23154)
-- Name: workspaces workspaces_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


-- Completed on 2025-12-27 15:11:45 WIB

--
-- PostgreSQL database dump complete
--

\unrestrict 0IsM8CBPaXcT7h5tYsJllONRbNOkK8EGuYmqljmhpCUqsd1lfvenU9WAKqyAQpb

